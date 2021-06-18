<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCauseData;
use App\DataFixtures\ORM\LoadCoalitionData;
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

        $this->assertCount(8, $causes = $crawler->filter('.datagrid table tbody tr'));
        $causeFields = $causes->eq(6)->filter('td');
        $this->assertSame('2', $causeFields->eq(1)->text());
        $this->assertSame('Cause en attente', $causeFields->eq(2)->text());
        $this->assertStringContainsString('Jacques Picard', $causeFields->eq(3)->text());
        $this->assertStringContainsString('(Paris 8e)', $causeFields->eq(3)->text());
        $this->assertStringContainsString('jacques.picard@en-marche.fr', $causeFields->eq(3)->text());
        $this->assertStringContainsString('+33 1 87 26 42 36', $causeFields->eq(3)->text());
        $this->assertSame('Justice', $causeFields->eq(5)->text());
        $this->assertSame('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $causeFields->eq(6)->text(null, true));
        $this->assertSame('En attente', $causeFields->eq(8)->text());
    }

    /**
     * @dataProvider getCausesFilterParameters
     */
    public function testCausesCanBeFiltered(array $parameters, int $expectedResultCount): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/causes');
        $this->assertResponseIsSuccessful();
        $this->assertCount(8, $causes = $crawler->filter('.datagrid table tbody tr'));

        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'f' => $parameters,
        ]));
        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedResultCount, $causes = $crawler->filter('.datagrid table tbody tr'));

        $crawler = $this->client->clickLink('Réinitialiser le filtre');
        $this->assertResponseIsSuccessful();
        $this->assertCount(8, $causes = $crawler->filter('.datagrid table tbody tr'));
    }

    public function getCausesFilterParameters(): iterable
    {
        yield [['name' => 'éducation'], 1];
        yield [['name' => 'cultur'], 3];
        yield [['primaryCoalition' => 4], 1];
        yield [['secondaryCoalition' => 4], 2];
        yield [['authorFirstName' => 'jacq'], 5];
        yield [['authorLastName' => 'mira'], 3];
        yield [['createdAfter' => (new \DateTime('-3 days'))->format('Y-m-d')], 4];
        yield [['createdBefore' => (new \DateTime('-1 day'))->format('Y-m-d')], 6];
        yield [[
            'createdAfter' => (new \DateTime('-3 days'))->format('Y-m-d'),
            'createdBefore' => (new \DateTime('-1 day'))->format('Y-m-d'),
        ], 2];
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

    public function testEditCauseAsCoalitionModeratorFailed(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/causes');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->click($crawler->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Modifier la cause', $crawler->filter('.manager-content h3')->text());

        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form(['cause' => [
            '_token' => $crawler->filter('input[name="cause[_token]"]')->attr('value'),
            'name' => '',
            'description' => '',
            'coalition' => null,
            'secondCoalition' => null,
            // disabled user
            'author' => LoadAdherentData::ADHERENT_1_UUID,
        ]]));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(3, $crawler->filter('li.form__error'));
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#cause_name_errors li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#cause_coalition_errors li')->text());
        $this->assertSame("L'utilisateur n'est pas un utilisateur Coalitions actif.", $crawler->filter('#cause_errors li')->text());

        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form(['cause' => [
            '_token' => $crawler->filter('input[name="cause[_token]"]')->attr('value'),
            'name' => 'Test',
            'description' => 'Test',
            'coalition' => $this->getCoalitionRepository()->findOneBy([
                'uuid' => LoadCoalitionData::COALITION_1_UUID,
            ])->getId(),
            'secondCoalition' => $this->getCoalitionRepository()->findOneBy([
                'uuid' => LoadCoalitionData::COALITION_1_UUID,
            ])->getId(),
        ]]));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(2, $errors = $crawler->filter('li.form__error'));
        $this->assertSame('Veuillez choisir une autre coalition en tant que secondaire.', $errors->eq(0)->text());
        $this->assertSame("L'utilisateur n'est pas un utilisateur Coalitions actif.", $errors->eq(1)->text());
    }

    public function testEditCauseAsCoalitionModeratorSuccessful(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/causes');

        /** @var Cause $cause */
        $cause = $this->getCauseRepository()->findOneBy([
            'id' => $crawler->filter('table.datagrid__table-manager tbody tr td')->eq(1)->text(),
        ]);
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_5_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertFalse($cause->hasFollower($adherent));

        $crawler = $this->client->click($crawler->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Modifier la cause', $crawler->filter('.manager-content h3')->text());

        $this->client->submit($crawler->selectButton('Enregistrer')->form(['cause' => [
            '_token' => $crawler->filter('input[name="cause[_token]"]')->attr('value'),
            'name' => 'Cause avec un nouveau objectif',
            'description' => 'Nouvelle description.',
            'coalition' => $this->getCoalitionRepository()->findOneBy([
                'uuid' => LoadCoalitionData::COALITION_1_UUID,
            ])->getId(),
            'secondCoalition' => $this->getCoalitionRepository()->findOneBy([
                'uuid' => LoadCoalitionData::COALITION_2_UUID,
            ])->getId(),
            'author' => LoadAdherentData::ADHERENT_5_UUID,
        ]]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-coalition/causes', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(0, $errors = $crawler->filter('li.form__error'));
        $this->assertSame(
            'La cause "Cause avec un nouveau objectif" a bien été modifiée.',
            $crawler->filter('.flash .flash__inner')->eq(0)->text()
        );
        $fields = $crawler->filter('table.datagrid__table-manager tbody tr')->eq(0)->filter('td');
        $this->assertSame('Cause avec un nouveau objectif', $fields->eq(2)->text());
        $this->assertSame('Culture, Démocratie', $fields->eq(5)->text());
        $this->assertSame('Nouvelle description.', $fields->eq(6)->text(null, true));
        $this->assertSame('Gisele Berthoux (Clichy)', $fields->eq(3)->filter('.adherent-name')->text(null, true));
        $this->assertTrue($cause->hasFollower($adherent));
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
        self::assertMatchesRegularExpression(
            '/^attachment; filename="causes--[\d-]{17}.csv"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('Id,Statut,Coalition,Zone,Ville,Soutiens,Objectif,Description,Adhérent,Prénom,Nom,Image,Url,"Date de création"', $responseContent);
        $this->assertStringContainsString('8,Approuvée,Justice,75008,"Paris 8e",0,"Cause pour la justice","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Jacques,Picard,,http://coalitions.code/cause/44249b1d-ea10-41e0-b288-5eb74fa886ba,', $responseContent);
        $this->assertStringContainsString('7,Approuvée,Éducation,75008,"Paris 8e",0,"Cause pour l\'éducation","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Jacques,Picard,http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png,http://coalitions.code/cause/fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8', $responseContent);
        $this->assertStringContainsString('6,Approuvée,Culture,73100,Mouxy,0,"Cause pour la culture 3","Description de la cause pour la culture 3",Oui,Carl,Mirabeau,,http://coalitions.code/cause/5f8a6d40-9e69-4311-a45b-67c00d30ad41', $responseContent);
        $this->assertStringContainsString('5,Approuvée,Culture,73100,Mouxy,0,"Cause pour la culture 2","Description de la cause pour la culture 2",Oui,Carl,Mirabeau,http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png,http://coalitions.code/cause/017491f9-1953-482e-b491-20418235af1f,', $responseContent);
        $this->assertStringContainsString('4,Approuvée,Culture,73100,Mouxy,5,"Cause pour la culture","Lorem ipsum dolor sit amet, consectetur adipiscing elit.",Oui,Carl,Mirabeau,http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png,http://coalitions.code/cause/55056e7c-2b5f-4ef6-880e-cde0511f79b2', $responseContent);
        $this->assertStringContainsString('3,Refusée,Culture,75008,"Paris 8e",0,"Cause refusée","Pellentesque aliquam neque nec volutpat ultricies.",Oui,Jacques,Picard,,http://coalitions.code/cause/227a2360-f4b3-4e3f-8f85-5b1700300ca9', $responseContent);
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
