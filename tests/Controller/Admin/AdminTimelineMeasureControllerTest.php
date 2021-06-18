<?php

namespace Tests\App\Controller\Admin;

use Algolia\SearchBundle\SearchService;
use App\DataFixtures\ORM\LoadTimelineData;
use App\Entity\Timeline\Manifesto;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Profile;
use App\Entity\Timeline\Theme;
use App\Repository\Timeline\ManifestoRepository;
use App\Repository\Timeline\MeasureRepository;
use App\Repository\Timeline\ProfileRepository;
use App\Repository\Timeline\ThemeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Algolia\DummySearchService;

/**
 * @group functional
 * @group admin
 */
class AdminTimelineMeasureControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var MeasureRepository
     */
    private $measureRepository;

    /**
     * @var ThemeRepository
     */
    private $themeRepository;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var ManifestoRepository
     */
    private $manifestoRepository;

    public function testUnindexedMeasureAfterMeasureRemoval()
    {
        /* @var $measure Measure */
        $measure = $this->measureRepository->findOneByTitle(LoadTimelineData::MEASURES['TM001']['title']['fr']);

        $this->authenticateAsAdmin($this->client);

        $deleteUrl = sprintf('/admin/app/timeline-measure/%s/delete', $measure->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $deleteUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, supprimer')->form());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertSame(1, $this->getIndexer()->countForUnIndexByType(Measure::class));
        $this->assertSame(1, $this->getIndexer()->countForIndexByType(Theme::class));
    }

    public function testIndexedThemesAfterMeasureUpdate()
    {
        /* @var $measure Measure */
        $measure = $this->measureRepository->findOneByTitle(LoadTimelineData::MEASURES['TM001']['title']['fr']);
        $currentTheme = $this->themeRepository->findOneByTitle(LoadTimelineData::THEMES['TT001']['title']['fr']);
        $newTheme = $this->themeRepository->findOneByTitle(LoadTimelineData::THEMES['TT003']['title']['fr']);

        $this->assertTrue($measure->getThemes()->contains($currentTheme));
        $this->assertFalse($measure->getThemes()->contains($newTheme));
        $this->assertTrue($measure->getThemesToIndex()->contains($currentTheme));
        $this->assertFalse($measure->getThemesToIndex()->contains($newTheme));

        $this->authenticateAsAdmin($this->client);

        $editUrl = sprintf('/admin/app/timeline-measure/%s/edit', $measure->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );

        $this->client->submit($crawler->selectButton('Mettre à jour')->form([
            $formName.'[themes]' => [$newTheme->getId()],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertSame(1, $this->getIndexer()->countForIndexByType(Measure::class));
        $this->assertSame(2, $this->getIndexer()->countForIndexByType(Theme::class));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->measureRepository = $this->getRepository(Measure::class);
        $this->themeRepository = $this->getRepository(Theme::class);
        $this->profileRepository = $this->getRepository(Profile::class);
        $this->manifestoRepository = $this->getRepository(Manifesto::class);
    }

    protected function tearDown(): void
    {
        $this->measureRepository = null;
        $this->themeRepository = null;
        $this->profileRepository = null;
        $this->manifestoRepository = null;

        parent::tearDown();
    }

    private function getIndexer(): DummySearchService
    {
        return $this->client->getContainer()->get(SearchService::class);
    }
}
