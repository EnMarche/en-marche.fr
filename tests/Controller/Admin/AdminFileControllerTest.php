<?php

namespace Tests\App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class AdminFileControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideAdherentsWithNoAccess
     */
    public function testAdherentCannotDownloadFileIfNoPermission(string $adherentEmail)
    {
        $image = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, sprintf('admin/filesystem/documents/%s', $image->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdminCannotDownloadFileIfNoPermission()
    {
        $image = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdmin($this->client, 'writer@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/admin/filesystem/documents/%s', $image->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCannotDownloadDirectory()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'images']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/admin/filesystem/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testDownloadExternalLink()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'dpt link for all']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/admin/filesystem/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        self::assertClientIsRedirectedTo('https://dpt.en-marche.fr', $this->client);
    }

    public function testCannotDownloadFileIfNoOnStorage()
    {
        $directory = $this->getFileRepository()->findOneBy(['name' => 'Image for all']);

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/admin/filesystem/documents/%s', $directory->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function provideAdherentsWithNoAccess(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['jacques.picard@en-marche.fr'];
    }
}
