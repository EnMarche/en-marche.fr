<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadCauseData;
use App\Entity\Coalition\Cause;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CausesControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $causeRepository;

    /** @dataProvider providePages */
    public function testCausesPageIsForbiddenAsNotCoalitionModerator(string $path): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testSeeCausesPageAsCoalitionModerator(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/causes');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertCount(7, $causes = $crawler->filter('.datagrid table tbody tr'));
        $causeFields = $causes->eq(5)->filter('td');
        $this->assertSame('2', $causeFields->eq(1)->text());
        $this->assertSame('Cause en attente', $causeFields->eq(2)->text());
        $this->assertStringContainsString('Jacques (Paris 8e)', $causeFields->eq(3)->text());
        $this->assertStringContainsString('jacques.picard@en-marche.fr', $causeFields->eq(3)->text());
        $this->assertStringContainsString('+33 1 87 26 42 36', $causeFields->eq(3)->text());
        $this->assertSame('Justice', $causeFields->eq(5)->text());
        $this->assertSame('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $causeFields->eq(6)->text(null, true));
        $this->assertSame('En attente', $causeFields->eq(8)->text());
    }

    public function testChangeCauseStatusAsCoalitionModerator(): void
    {
        /** @var Cause $cause */
        $cause = $this->causeRepository->findOneBy(['uuid' => LoadCauseData::CAUSE_7_UUID]);

        $this->assertSame(Cause::STATUS_PENDING, $cause->getStatus());

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        // approve
        $this->client->request(Request::METHOD_POST, '/espace-coalition/causes/approuver',
            ['ids' => [$cause->getId()]],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->manager->clear();

        $this->assertSame(Cause::STATUS_APPROVED, $cause->getStatus());
        $this->assertCountMails(1, 'CauseApprovalMessage', 'jacques.picard@en-marche.fr');

        // refuse
        $this->client->request(Request::METHOD_POST, '/espace-coalition/causes/refuser',
            ['ids' => [$cause->getId()]],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->manager->clear();
        $cause = $this->causeRepository->findOneBy(['uuid' => LoadCauseData::CAUSE_7_UUID]);

        $this->assertSame(Cause::STATUS_REFUSED, $cause->getStatus());

        // approve
        $this->client->request(Request::METHOD_POST, '/espace-coalition/causes/approuver',
            ['ids' => [$cause->getId()]],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->manager->clear();
        $cause = $this->causeRepository->findOneBy(['uuid' => LoadCauseData::CAUSE_7_UUID]);

        $this->assertSame(Cause::STATUS_APPROVED, $cause->getStatus());
        $this->assertCountMails(2, 'CauseApprovalMessage', 'jacques.picard@en-marche.fr');
    }

    public function testEditCauseAsCoalitionModerator(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/causes');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->click($crawler->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Modifier la cause', $crawler->filter('.manager-content h3')->text());

        // with invalid
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form(['cause' => [
            '_token' => $crawler->filter('input[name="cause[_token]"]')->attr('value'),
            'name' => '',
        ]]));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(1, $errors = $crawler->filter('li.form__error'));
        $this->assertSame('Cette valeur ne doit pas être vide.', $errors->text());

        // with correct data
        $this->client->submit($crawler->selectButton('Enregistrer')->form(['cause' => [
            '_token' => $crawler->filter('input[name="cause[_token]"]')->attr('value'),
            'name' => 'Cause avec un nouveau objectif',
        ]]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-coalition/causes', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(0, $errors = $crawler->filter('li.form__error'));
        $this->assertSame(
            'La cause "Cause avec un nouveau objectif" a bien été modifiée.',
            $crawler->filter('.flash .flash__inner')->eq(0)->text()
        );
    }

    public function testExportCausesCsvIsForbiddenAsNotCoalitionModerator(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coalition/causes.csv');

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testExportCausesCsv(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        ob_start();
        $this->client->request(Request::METHOD_GET, '/espace-coalition/causes.csv');
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertRegExp(
            '/^attachment; filename="causes--[\d-]{17}.csv"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('Id,Statut,Coalition,Zone,Ville,Soutiens,Objectif,Description,Adhérent,Prénom,Nom,Image,Url,"Date de création"', $responseContent);
        $this->assertStringContainsString('7,Approuvée,Justice,75008,"Paris 8e",0,"Cause pour la justice","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Jacques,Picard,,http://coalitions.code/cause/44249b1d-ea10-41e0-b288-5eb74fa886ba,', $responseContent);
        $this->assertStringContainsString('6,Approuvée,Education,75008,"Paris 8e",0,"Cause pour l\'education","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Jacques,Picard,http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png,http://coalitions.code/cause/fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8', $responseContent);
        $this->assertStringContainsString('5,Approuvée,Culture,8057,Zürich,0,"Cause pour la culture 3","Description de la cause pour la culture 3",Oui,Michelle,Dufour,,http://coalitions.code/cause/5f8a6d40-9e69-4311-a45b-67c00d30ad41', $responseContent);
        $this->assertStringContainsString('4,Approuvée,Culture,8057,Zürich,0,"Cause pour la culture 2","Description de la cause pour la culture 2",Oui,Michelle,Dufour,http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png,http://coalitions.code/cause/017491f9-1953-482e-b491-20418235af1f,', $responseContent);
        $this->assertStringContainsString('3,Approuvée,Culture,8057,Zürich,5,"Cause pour la culture","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Michelle,Dufour,http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png,http://coalitions.code/cause/55056e7c-2b5f-4ef6-880e-cde0511f79b2', $responseContent);
        $this->assertStringContainsString('2,"En attente",Justice,75008,"Paris 8e",0,"Cause en attente","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Jacques,Picard,,http://coalitions.code/cause/253b0ed7-7426-15f8-97f9-2bb32d0a4d17,', $responseContent);
        $this->assertStringContainsString('1,Approuvée,Inactive,75008,"Paris 8e",0,"Cause d\'une coalition désactivée","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Jacques,Picard,,http://coalitions.code/cause/13814069-1dd2-11b2-98d6-2fdf8179626a,', $responseContent);
    }

    public function providePages(): iterable
    {
        yield ['/espace-coalition/causes'];
        yield ['/espace-coalition/causes/cause-pour-la-culture/editer'];
        yield ['/espace-coalition/causes/cause-en-attente/editer'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->causeRepository = $this->getCauseRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->causeRepository = null;

        parent::tearDown();
    }
}
