<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\MemberSummary\Language;
use App\Form\SummaryType;
use App\Membership\ActivityPositions;
use App\Summary\Contract;
use App\Summary\Contribution;
use App\Summary\JobDuration;
use App\Summary\JobLocation;
use League\Glide\Signatures\SignatureFactory;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group summary
 * @group debug
 */
class SummaryManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const SECTION_HEADER = 'summary.header';
    private const SECTION_SYNTHESIS = 'summary.synthesis';
    private const SECTION_MISSIONS = 'summary.missions';
    private const SECTION_MOTIVATION = 'summary.motivation';
    private const SECTION_EXPERIENCES = 'summary.experiences';
    private const SECTION_RECENT_ACTIVITIES = 'summary.recent_activities';
    private const SECTION_SKILLS = 'summary.skills';
    private const SECTION_LANGUAGES = 'summary.languages';
    private const SECTION_TRAININGS = 'summary.trainings';
    private const SECTION_INTERESTS = 'summary.interests';

    private const SECTIONS = [
        self::SECTION_HEADER,
        self::SECTION_SYNTHESIS,
        self::SECTION_MISSIONS,
        self::SECTION_MOTIVATION,
        self::SECTION_RECENT_ACTIVITIES,
        self::SECTION_SKILLS,
        self::SECTION_LANGUAGES,
        self::SECTION_EXPERIENCES,
        self::SECTION_TRAININGS,
        self::SECTION_INTERESTS,
    ];

    public function provideActions()
    {
        yield 'Index' => ['/espace-adherent/mon-profil'];
        yield 'Handle experience' => ['/espace-adherent/mon-profil/experience'];
        yield 'Handle training' => ['/espace-adherent/mon-profil/formation'];
        yield 'Handle language' => ['/espace-adherent/mon-profil/langue'];

        foreach (SummaryType::STEPS as $step) {
            yield 'Handle step '.$step => ['/espace-adherent/mon-profil/'.$step];
        }
    }

    /**
     * @dataProvider provideActions
     */
    public function testActionsAreForbiddenAsAnonymous(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider provideActions
     */
    public function testActionsAreSuccessfulAsAdherentWithSummary(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @dataProvider provideActions
     */
    public function testActionsAreSuccessfulAsAdherentWithoutSummary(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateExperience()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(0, $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(8, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-experiences .summary-add-item')->link());

        $company = 'Example';
        $position = 'Tester';

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[company]' => $company,
            'job_experience[website]' => 'example.org',
            'job_experience[position]' => $position,
            'job_experience[location]' => 'Somewhere over the rainbow',
            'job_experience[started_at][month]' => '2',
            'job_experience[started_at][year]' => '2012',
            'job_experience[ended_at][month]' => '2',
            'job_experience[ended_at][year]' => '2012',
            'job_experience[contract]' => Contract::TEMPORARY,
            'job_experience[duration]' => JobDuration::FULL_TIME,
            'job_experience[description]' => 'Lorem ipsum',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSummaryCompletion(15, $crawler);
        $this->assertSame('L\'expérience a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(1, $experience = $crawler->filter('.cv__experience > div'));
        $this->assertStringContainsString(strtoupper($position), $experience->filter('h3')->text());
        $this->assertSame($company, $experience->filter('h4')->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(2, $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-experiences .summary-add-item')->link());

        $company = 'Example';

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[company]' => $company,
            'job_experience[website]' => 'example.org',
            'job_experience[position]' => 'Tester',
            'job_experience[location]' => 'Somewhere over the rainbow',
            'job_experience[started_at][month]' => '2',
            'job_experience[started_at][year]' => '2012',
            'job_experience[ended_at][month]' => '2',
            'job_experience[ended_at][year]' => '2012',
            'job_experience[contract]' => Contract::TEMPORARY,
            'job_experience[duration]' => JobDuration::FULL_TIME,
            'job_experience[description]' => 'Lorem ipsum',
            'job_experience[display_order][entry]' => '1',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(3, $experiences = $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame($company, $experiences->eq(0)->filter('h4')->text());

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(3, $experiences = $summary->getExperiences());

        foreach ($experiences as $experience) {
            switch ($experience->getDisplayOrder()) {
                case 1:
                    $this->assertSame('Example', $experience->getCompany());

                    break;
                case 2:
                    $this->assertSame('Institut KNURE', $experience->getCompany());

                    break;
                case 3:
                    $this->assertSame('Univérsité Lyon 1', $experience->getCompany());

                    break;
            }
        }
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testEditExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(2, $experiences = $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);

        $lastExperience = $experiences->eq(1);

        $this->assertSame('Univérsité Lyon 1', $lastExperience->filter('h4')->text());

        $crawler = $this->client->click($lastExperience->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $newPosition = 1;

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[display_order][entry]' => $newPosition,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(2, $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('Univérsité Lyon 1', $crawler->filter('.cv__experience h4')->eq(0)->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testDeleteExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(2, $experiences = $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);

        $lastExperience = $experiences->eq(1);

        $this->assertSame('Univérsité Lyon 1', $lastExperience->filter('h4')->text());

        $this->client->submit($crawler->filter('.cv__experience')->eq(0)->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(1, $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(1, $experiences = $summary->getExperiences());

        $firstExperience = $experiences->first();

        $this->assertSame('Univérsité Lyon 1', $firstExperience->getCompany());
        $this->assertSame(1, $firstExperience->getDisplayOrder());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateTraining()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(0, $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(8, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-trainings .summary-add-item')->link());

        $organization = 'EXAMPLE';
        $diploma = 'MASTER';
        $studyField = 'WEB DEVELOPMENT';

        $this->client->submit($crawler->filter('form[name=training]')->form([
            'training[organization]' => $organization,
            'training[diploma]' => $diploma,
            'training[study_field]' => $studyField,
            'training[started_at][month]' => '2',
            'training[started_at][year]' => '2012',
            'training[ended_at][month]' => '2',
            'training[ended_at][year]' => '2012',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSummaryCompletion(15, $crawler);
        $this->assertSame('La formation a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(1, $experience = $crawler->filter('.cv__training'));
        $this->assertSame($diploma.' - '.$studyField, $experience->filter('h3')->text());
        $this->assertSame(strtoupper($organization), $experience->filter('h4')->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateTrainingChangesOrder()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(2, $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(100, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-trainings .summary-add-item')->link());

        $diploma = 'MASTER';

        $this->client->submit($crawler->filter('form[name=training]')->form([
            'training[organization]' => 'Example',
            'training[diploma]' => $diploma,
            'training[study_field]' => 'Web development',
            'training[started_at][month]' => '2',
            'training[started_at][year]' => '2012',
            'training[ended_at][month]' => '2',
            'training[ended_at][year]' => '2012',
            'training[display_order][entry]' => '1',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(3, $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertStringContainsString($diploma, $crawler->filter('.cv__training h3')->eq(0)->text());

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(3, $trainings = $summary->getTrainings());

        foreach ($trainings as $training) {
            switch ($training->getDisplayOrder()) {
                case 1:
                    $this->assertSame($diploma, $training->getDiploma());

                    break;
                case 2:
                    $this->assertSame('Diplôme d\'ingénieur', $training->getDiploma());

                    break;
                case 3:
                    $this->assertSame('DUT Génie biologique', $training->getDiploma());

                    break;
            }
        }
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testEditTrainingChangesOrder()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(2, $trainings = $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(100, $crawler);

        $lastTraining = $trainings->eq(1);

        $this->assertSame('DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE', $lastTraining->filter('h3')->text());

        $crawler = $this->client->click($lastTraining->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $newPosition = 1;

        $this->client->submit($crawler->filter('form[name=training]')->form([
            'training[display_order][entry]' => $newPosition,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(2, $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE', $crawler->filter('.cv__training h3')->eq(0)->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testDeleteTrainingChangesOrder()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(2, $trainings = $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(100, $crawler);

        $lastTraining = $trainings->eq(1);

        $this->assertSame('DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE', $lastTraining->filter('h3')->text());

        $this->client->submit($crawler->filter('.cv__training')->eq(0)->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(1, $crawler->filter('.cv__training'));
        $this->assertSummaryCompletion(100, $crawler);

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(1, $trainings = $summary->getTrainings());

        $firstTraining = $trainings->first();

        $this->assertSame('DUT Génie biologique', $firstTraining->getDiploma());
        $this->assertSame(1, $firstTraining->getDisplayOrder());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateLanguageWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(0, $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(8, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-languages .summary-add-item')->link());

        $code = 'fr';
        $level = Language::LEVEL_FLUENT;

        $this->client->submit($crawler->filter('form[name=language]')->form([
            'language[code]' => $code,
            'language[level]' => $level,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('La langue a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(1, $language = $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(15, $crawler);
        $this->assertSame('Français - '.ucfirst($level), $language->filter('.cv__languages > div')->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateLanguageWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(3, $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(100, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-languages .summary-add-item')->link());

        $code = 'fr';
        $level = Language::LEVEL_FLUENT;

        $this->client->submit($crawler->filter('form[name=language]')->form([
            'language[code]' => $code,
            'language[level]' => $level,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertCount(4, $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(100, $crawler);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testEditLanguage()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertSummaryCompletion(100, $crawler);

        $firstLanguage = $crawler->filter('.cv__languages')->eq(2);

        $this->assertSame('Espagnol - Bonne maîtrise', $firstLanguage->filter('.cv__languages > div')->text());

        $crawler = $this->client->click($firstLanguage->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $newLevel = Language::LEVEL_HIGH;

        $this->client->submit($crawler->filter('form[name=language]')->form([
            'language[level]' => $newLevel,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(3, $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('Espagnol - Maîtrise parfaite', $crawler->filter('.cv__languages > div')->eq(2)->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testDeleteLanguage()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(3, $languages = $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(100, $crawler);

        $firstLanguage = $languages->eq(0);

        $this->assertSame('Français - Langue maternelle', $firstLanguage->filter('.cv__languages > div')->text());

        $this->client->submit($firstLanguage->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(2, $crawler->filter('.cv__languages'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('Anglais - Maîtrise parfaite', $crawler->filter('.cv__languages > div')->eq(0)->text());
    }

    public function testSearchSkillUserHasNot()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        // Search the skill that user has not, should find one skill
        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/competences/autocompletion?term=outi', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $this->assertNotEmpty(\GuzzleHttp\json_decode($content, true));
        $skills = \GuzzleHttp\json_decode($content, true);
        $this->assertSame(1, \count($skills));
        foreach ($skills as $skill) {
            $this->assertSame('Outils médias', $skill);
        }
    }

    public function testSearchSkillUserHas()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        // Search the skill that user already has, nothing should be found
        $this->client->request(Request::METHOD_GET, 'espace-adherent/mon-profil/competences/autocompletion?term=sof', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $this->assertEmpty(\GuzzleHttp\json_decode($content, true));
        $this->client->followRedirects(true);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testAddSkillWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(0, $crawler->filter('.summary-skill'));
        $this->assertSummaryCompletion(8, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-skills .summary-modify')->link());

        $skill1 = 'Développement';
        $skill2 = 'Gestion des bases';

        $skillCollection = $crawler->filter('#summary_skills')->getNode(0);
        $this->appendCollectionFormPrototype($skillCollection, '0');
        $this->appendCollectionFormPrototype($skillCollection, '1');

        $this->client->submit($crawler->filter('form[name=summary]')->form(), [
            'summary[skills][0][name]' => $skill1,
            'summary[skills][1][name]' => $skill2,
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(2, $skills = $crawler->filter('.cv__skills li'));
        $this->assertSame($skill1, $skills->eq(0)->filter('li')->text());
        $this->assertSame($skill2, $skills->eq(1)->filter('li')->text());
        $this->assertSummaryCompletion(15, $crawler);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testModifySkillsWithSummary()
    {
        // This adherent has a summary and skills already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertCount(4, $crawler->filter('.cv__skills li'));
        $this->assertSummaryCompletion(100, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-skills .summary-modify')->link());

        $skill1 = 'Développement';
        $skill2 = 'Gestion des bases';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[skills][1][name]' => $skill1,
            'summary[skills][2][name]' => $skill2,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(4, $crawler->filter('.cv__skills li'));
        $this->assertStringContainsString($skill1, $skills = $crawler->filter('.cv__skills')->text());
        $this->assertStringContainsString($skill2, $skills);
        $this->assertSummaryCompletion(100, $crawler);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testAddSkillWithMoreThan200Characters(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/skills');

        $skill1 = 'Plomberie';
        $skill2 = 'Une compétence qui fait plus de 200 caractèters ne doit pas faire une 500................................................................................................................................';

        $skillCollection = $crawler->filter('#summary_skills')->getNode(0);
        $this->appendCollectionFormPrototype($skillCollection, '0');
        $this->appendCollectionFormPrototype($skillCollection, '1');

        $crawler = $this->client->submit($crawler->filter('form[name=summary]')->form(), [
            'summary[skills][0][name]' => $skill1,
            'summary[skills][1][name]' => $skill2,
        ]);

        $formErrors = $crawler->filter('.form__errors li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(1, $formErrors->count());
        $this->assertSame('Vous devez saisir au maximum 200 caractères.', $formErrors->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepSynthesisWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/synthesis');

        $this->assertCount(11, $crawler->filter('form[name=summary] input'));
        $this->assertCount(1, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $profession = 'Professeur';
        $synopsis = 'This should be a professional synopsis.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[current_profession]' => $profession,
            'summary[current_position]' => ActivityPositions::UNEMPLOYED,
            'summary[contribution_wish]' => Contribution::VOLUNTEER,
            'summary[availabilities]' => [JobDuration::PART_TIME],
            'summary[job_locations][0]' => JobLocation::ON_SITE,
            'summary[job_locations][1]' => JobLocation::ON_REMOTE,
            'summary[professional_synopsis]' => $synopsis,
            'summary[personal_data_collection]' => true,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(36, $crawler);

        $header = $this->getSummarySection($crawler, self::SECTION_HEADER);
        $synthesis = $this->getSummarySection($crawler, self::SECTION_SYNTHESIS);

        $this->assertSame('En recherche d\'emploi', $header->filter('.cv__header__position')->text());
        $this->assertCount(1, $header->filter(sprintf('p:contains("%s")', $synopsis)));
        $this->assertCount(1, $synthesis->filter('.summary-contributionwish:contains("Missions de bénévolat")'));
        $this->assertCount(1, $synthesis->filter('.summary-availability:contains("Temps partiel")'));
        $this->assertCount(1, $synthesis->filter('.summary-location:contains("Sur site ou à distance")'));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepSynthesisWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/synthesis');

        $this->assertCount(11, $crawler->filter('form[name=summary] input'));
        $this->assertCount(1, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $profession = 'Professeur';
        $synopsis = 'This should be a professional synopsis.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[current_profession]' => $profession,
            'summary[current_position]' => ActivityPositions::UNEMPLOYED,
            'summary[contribution_wish]' => Contribution::VOLUNTEER,
            'summary[availabilities]' => [JobDuration::PART_TIME],
            'summary[job_locations][1]' => JobLocation::ON_REMOTE,
            'summary[professional_synopsis]' => $synopsis,
            'summary[personal_data_collection]' => true,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $header = $this->getSummarySection($crawler, self::SECTION_HEADER);
        $synthesis = $this->getSummarySection($crawler, self::SECTION_SYNTHESIS);

        $this->assertCount(1, $header->filter(sprintf('p:contains("%s")', $synopsis)));
        $this->assertCount(1, $synthesis->filter('.summary-contributionwish:contains("Missions de bénévolat")'));
        $this->assertCount(1, $synthesis->filter('.summary-availability:contains("Temps partiel")'));
        $this->assertCount(1, $synthesis->filter('.summary-location:contains("À distance")'));
    }

    public function testStepMissionsWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/missions');

        $this->assertCount(10, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $form = $crawler->filter('form[name=summary]')->form();

        $form['summary[mission_type_wishes]'][0]->tick();
        $form['summary[mission_type_wishes]'][2]->tick();
        $form['summary[mission_type_wishes]'][4]->tick();

        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(15, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_MISSIONS);

        $this->assertCount(3, $wishes = $missions->filter('.summary-wish'));

        for ($i = 0; $i < $wishes->count(); ++$i) {
            $this->assertContains(trim($wishes->eq($i)->text()), [
                'Me former à l\'action politique et citoyenne',
                'Faire remonter les opinions du terrain',
                'Expérimenter des projets concrets',
            ]);
        }
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMissionsWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/missions');

        $this->assertCount(10, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $form = $crawler->filter('form[name=summary]')->form();

        $form['summary[mission_type_wishes]'][1]->tick();
        $form['summary[mission_type_wishes]'][3]->tick();
        $form['summary[mission_type_wishes]'][6]->tick();

        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_MISSIONS);

        $this->assertCount(4, $missions->filter('.summary-wish'));
        $this->assertSame('Faire émerger des idées nouvelles', trim($missions->filter('.summary-wish')->eq(0)->text()));
        $this->assertSame('Faire remonter les opinions du terrain', trim($missions->filter('.summary-wish')->eq(1)->text()));
        $this->assertSame('Recruter des nouveaux adhérents et attirer de nouveaux talents', trim($missions->filter('.summary-wish')->eq(3)->text()));
        $this->assertSame('M\'engager dans des projets citoyens concrètes', trim($missions->filter('.summary-wish')->eq(2)->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMotivationWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/motivation');

        $this->assertCount(1, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $motivation = 'I\'m motivated.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[motivation]' => $motivation,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(15, $crawler);

        $section = $this->getSummarySection($crawler, self::SECTION_MOTIVATION);

        $this->assertCount(1, $section->filter('.cv__motivation'));
        $this->assertSame($motivation, trim($section->filter('.cv__motivation')->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMotivationWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/motivation');

        $this->assertCount(1, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $motivation = 'I\'m motivated.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[motivation]' => $motivation,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $section = $this->getSummarySection($crawler, self::SECTION_MOTIVATION);

        $this->assertCount(1, $section->filter('.cv__motivation'));
        $this->assertSame($motivation, trim($section->filter('.cv__motivation')->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepInterestsWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/interests');

        $this->assertCount(20, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[member_interests][0]' => 'agriculture',
            'summary[personal_data_collection]' => true,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(15, $crawler);

        $interests = $this->getSummarySection($crawler, self::SECTION_INTERESTS);

        $this->assertCount(1, $interests->filter('li'));
        $this->assertSame('Agriculture', trim($interests->filter('li')->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepInterestsWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/interests');

        $this->assertCount(20, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[member_interests][4]' => 'education',
            'summary[member_interests][10]' => 'jeunesse',
            'summary[personal_data_collection]' => true,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $interests = $this->getSummarySection($crawler, self::SECTION_INTERESTS);

        $this->assertCount(2, $interests->filter('li'));
        $this->assertSame('Jeunesse', trim($interests->filter('li')->eq(0)->text()));
        $this->assertSame('Éducation', trim($interests->filter('li')->eq(1)->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepContactWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/contact');

        $this->assertCount(8, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[contact_email]' => 'toto@example.org',
            'summary[personal_data_collection]' => true,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(15, $crawler);

        $this->assertCount(1, $crawler->filter('.summary-contact-email'));
        $this->assertCount(0, $crawler->filter('.summary-contact-facebook'));
        $this->assertCount(0, $crawler->filter('.summary-contact-linked_in'));
        $this->assertCount(0, $crawler->filter('.summary-contact-twitter'));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepContactWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/contact');

        $this->assertCount(8, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[contact_email]' => 'toto@example.org',
            'summary[linked_in_url]' => 'https://linkedin.com/in/lucieoliverafake',
            'summary[website_url]' => 'https://lucieoliverafake.com',
            'summary[facebook_url]' => 'https://facebook.com/lucieoliverafake',
            'summary[twitter_nickname]' => 'lucieoliverafake',
            'summary[viadeo_url]' => 'http://fr.viadeo.com/fr/profile/lucie.olivera.fake',
            'summary[personal_data_collection]' => true,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $contact = $this->getSummarySection($crawler, self::SECTION_HEADER);

        $this->assertCount(1, $contact->filter('.summary-contact-email'));
        $this->assertCount(1, $contact->filter('.summary-contact-facebook'));
        $this->assertCount(1, $contact->filter('.summary-contact-linked_in'));
        $this->assertCount(1, $contact->filter('.summary-contact-twitter'));
    }

    public function testPublishActionWithoutSummary()
    {
        $summariesCount = \count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/publier');

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $this->client->request(Request::METHOD_GET, '/membre/gisele-berthoux');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
    }

    public function testPublishingProcessWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/depublier');

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Votre profil a bien été dépublié.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $this->client->click($crawler->selectLink('Publier mon profil')->link());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);

        $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testAddProfilePictureAndCreateSummary()
    {
        // This adherent has no summary
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $this->assertFileInStorage('images/summaries/b4219d47-3138-5efd-9762-2ef9f9495084.jpg', false);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/photo');

        $files = [
            'summary' => [
                'error' => ['profile_picture' => \UPLOAD_ERR_OK],
                'name' => ['profile_picture' => 'image.jpg'],
                'size' => ['profile_picture' => 631],
                'tmp_name' => ['profile_picture' => __DIR__.'/../../Fixtures/image.jpg'],
                'type' => ['profile_picture' => 'image/jpeg'],
            ],
        ];

        $form = $crawler->filter('form[name=summary]')->form();

        $this->client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), $files);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);
        $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertFileInStorage('images/summaries/b4219d47-3138-5efd-9762-2ef9f9495084.jpg', true);
        unlink(__DIR__.'/../../../app/data/images/summaries/b4219d47-3138-5efd-9762-2ef9f9495084.jpg');
    }

    public function testAddProfilePictureToSummary()
    {
        // This adherent has a summary
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');
        $this->assertFileInStorage('images/summaries/29461c49-6316-5be1-9ac3-17816bf2d819.jpg', false);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil/photo');

        $files = [
            'summary' => [
                'error' => ['profile_picture' => \UPLOAD_ERR_OK],
                'name' => ['profile_picture' => 'image.jpg'],
                'size' => ['profile_picture' => 631],
                'tmp_name' => ['profile_picture' => __DIR__.'/../../Fixtures/image.jpg'],
                'type' => ['profile_picture' => 'image/jpeg'],
            ],
        ];

        $form = $crawler->filter('form[name=summary]')->form();
        $this->client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), $files);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $this->client);
        $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertFileInStorage('images/summaries/29461c49-6316-5be1-9ac3-17816bf2d819.jpg', true);
        unlink(__DIR__.'/../../../app/data/images/summaries/29461c49-6316-5be1-9ac3-17816bf2d819.jpg');
    }

    public function testShowHideRecentActivities()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');
        $this->assertCount(0, $crawler->filter('#summary-recent-activities p'));

        // Afficher au public
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');
        $this->client->click($crawler->selectLink('Afficher ces informations au public')->link());

        $crawler = $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');
        $this->assertCount(1, $crawler->filter('#summary-recent-activities p'));

        // Masquer au public
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');
        $this->client->click($crawler->selectLink('Masquer ces informations au public')->link());

        $crawler = $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');
        $this->assertCount(0, $crawler->filter('#summary-recent-activities p'));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }

    private function getSummarySection(Crawler $crawler, string $section): Crawler
    {
        return $crawler->filter('.adherent_cv section')->eq(array_search($section, self::SECTIONS));
    }

    private function assertSummaryCompletion(int $completion, Crawler $crawler)
    {
        $this->assertSame($completion.'%', trim($crawler->filter('.summary-completion')->text()));
    }

    private function assertFileInStorage(string $path, bool $isPresent = true)
    {
        $signature = SignatureFactory::create($this->client->getContainer()->getParameter('kernel.secret'))->generateSignature($path, []);

        $path = $this->client->getContainer()->get('router')->generate('asset_url', [
            'path' => $path,
            's' => $signature,
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        ob_start();
        $this->client->request(Request::METHOD_GET, $path);
        ob_end_clean();

        if ($isPresent) {
            $this->assertStatusCode(Response::HTTP_OK, $this->client);
        } else {
            $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
        }
    }
}
