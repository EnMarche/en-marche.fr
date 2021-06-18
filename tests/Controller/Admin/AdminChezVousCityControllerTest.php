<?php

namespace Tests\App\Controller\Admin;

use Algolia\SearchBundle\SearchService;
use App\Entity\ChezVous\City;
use App\Repository\ChezVous\CityRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Algolia\DummySearchService;

/**
 * @group functional
 * @group admin
 */
class AdminChezVousCityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var EntityRepository
     */
    private $cityRepository;

    public function testUnindexedCityAfterRemoval()
    {
        /** @var City $city */
        $city = $this->cityRepository->findOneByInseeCode('06088');

        $this->authenticateAsAdmin($this->client);

        $deleteUrl = sprintf('/admin/app/chezvous-city/%s/delete', $city->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $deleteUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, supprimer')->form());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $entitiesToIndex = $this->getIndexer()->getEntitiesToIndex();

        $this->assertSame(1, $this->getIndexer()->countForUnIndexByType(City::class));
        $this->assertEmpty($entitiesToIndex);
    }

    public function testIndexedCityAfterUpdate()
    {
        /* @var City $city */
        $city = $this->cityRepository->findOneByInseeCode('06088');

        $this->authenticateAsAdmin($this->client);

        $editUrl = sprintf('/admin/app/chezvous-city/%s/edit', $city->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );

        $this->client->submit($crawler->selectButton('Mettre à jour')->form([
            $formName.'[name]' => 'Nissa',
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertSame(1, $this->getIndexer()->countForIndexByType(City::class));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cityRepository = $this->get(CityRepository::class);
    }

    protected function tearDown(): void
    {
        $this->cityRepository = null;

        parent::tearDown();
    }

    private function getIndexer(): DummySearchService
    {
        return $this->client->getContainer()->get(SearchService::class);
    }
}
