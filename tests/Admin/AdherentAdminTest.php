<?php

namespace Tests\App\Admin;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdherentAdminTest extends AbstractWebCaseTest
{
    use ControllerTestTrait;

    private const ADHERENT_EDIT_URI_PATTERN = '/admin/app/adherent/%d/edit';

    private $adherentRepository;

    public function testAnAdminCantBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client);

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));

        $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/ban', $adherent->getId()));
        $this->assertResponseStatusCode(403, $this->client->getResponse());
    }

    public function testEditBoardMemberInformations()
    {
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertTrue($adherent->isBoardMember());
        $this->authenticateAsAdmin($this->client);
        $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId());
        // Empty roles should revoke board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->untick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill only area should not grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberArea]', $formName)] = 'metropolitan';
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill only roles should not grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill area and roles should grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberArea]', $formName)] = 'metropolitan';
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertTrue($adherent->isBoardMember());
    }

    public function testASuperAdminCanBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));
        $this->assertEquals('Exclure cet adhérent ⚠️', trim($navBar->getNode(3)->nodeValue));
        $this->assertEquals('Certifier cet adhérent', trim($navBar->getNode(4)->nodeValue));

        $link = $crawler->selectLink('Exclure cet adhérent ⚠️')->link();
        $crawler = $this->client->click($link);

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $this->assertStringContainsString(sprintf('L\'adhérent <b>%s</b> a bien été exclu', $adherent->getFullName()), $this->client->getResponse()->getContent());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
