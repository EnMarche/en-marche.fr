<?php

namespace Tests\App\Controller\EnMarche\Filesystem;

use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class FilesControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider getSpaceURIPrefixes
     */
    public function testFilesListIsDisplayedSuccessfully(
        string $uriPrefix,
        string $userEmail,
        int $expectedDocumentsNumber
    ): void {
        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/documents');

        self::assertSame($expectedDocumentsNumber, $crawler->filter('tbody tr')->count());
    }

    public function getSpaceURIPrefixes(): array
    {
        return [
            ['/espace-candidat', 'jacques.picard@en-marche.fr', 4],
            ['/espace-candidat', 'luciole1989@spambox.fr', 4],
            ['/espace-candidat', 'francis.brioul@yahoo.com', 4],
        ];
    }

    /**
     * @dataProvider provideSpaces
     */
    public function testDifferentSpaceCanBeDelegated(
        string $email,
        string $spaceLabel,
        string $uriPrefix,
        int $expectedDocumentsNumber
    ) {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request('GET', '/');
        self::assertStringContainsString($spaceLabel, $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());

        $this->client->click($crawler->selectLink($spaceLabel)->link());
        $this->assertResponseStatusCode(302, $this->client->getResponse());

        $this->client->followRedirect();

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, $uriPrefix.'/documents');

        self::assertSame($expectedDocumentsNumber, $crawler->filter('tbody tr')->count());
    }

    public function provideSpaces(): \Generator
    {
        yield ['gisele-berthoux@caramail.com', 'Espace candidat partagé (Île-de-France)', '/espace-candidat', 4];
    }
}
