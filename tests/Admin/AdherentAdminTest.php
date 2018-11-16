<?php

namespace Tests\AppBundle\Controller\Admin;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadAdminData;
use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group admin
 */
class AdherentAdminTest extends WebTestCase
{
    use ControllerTestTrait;

    private $adherentRepository;

    public function testAnAdminCantBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client);

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/edit', $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Afficher', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(2)->nodeValue));

        $this->client->request(Request::METHOD_GET, sprintf('/admin/adherent/%s/ban', $adherent->getId()));
        $this->assertResponseStatusCode(404, $this->client->getResponse());
    }

    public function testASuperAdminCanBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/edit', $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Afficher', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Bannir cet adhérent', trim($navBar->getNode(2)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(3)->nodeValue));

        $form = $crawler->filter('#ban')->form();
        $this->client->submit($form);

        $this->assertContains(sprintf('L\'adhérent %s a bien été banni', $adherent->getFullName()), $this->client->getResponse()->getContent());
    }

    public function testEditBoardMemberInformations()
    {
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);

        $this->assertTrue($adherent->isBoardMember());

        $this->authenticateAsAdmin($this->client);

        $editUrl = sprintf('/admin/app/adherent/%s/edit', $adherent->getId());

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

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdminData::class,
            LoadAdherentData::class,
        ]);

        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->adherentRepository = null;

        parent::tearDown();
    }
}
