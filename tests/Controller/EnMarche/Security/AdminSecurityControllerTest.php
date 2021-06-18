<?php

namespace Tests\App\Controller\EnMarche\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group security
 */
class AdminSecurityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testAuthenticationIsSuccessful(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('#auth-error'));

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'admin@en-marche-dev.fr',
            '_login_password' => 'admin',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/admin/dashboard', $this->client, true);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testAuthenticationIfSecretCode(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('#auth-error'));

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'titouan.galopin@en-marche.fr',
            '_login_password' => 'secret!12345',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/admin/dashboard', $this->client, true);

        $this->client->followRedirect();
        $this->assertClientIsRedirectedTo('/admin/2fa', $this->client, true);

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('#_auth_code'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_auth_code' => '123456',
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/admin/2fa', $this->client, true);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('#_auth_code'));
    }

    public function testAuthenticationFailedWhenAdminIsNotActivated(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'jean.dupond@en-marche.fr',
            '_login_password' => 'secret!12345',
        ]));

        $this->client->followRedirect();

        $this->assertStringContainsString(
            'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas.',
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @dataProvider provideInvalidCredentials
     */
    public function testLoginCheckFails(string $username, string $password): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('#auth-error'));

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => $username,
            '_login_password' => $password,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/admin/login', $this->client, true);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('#auth-error'));
        $this->assertSame('L\'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas.', trim($error->text()));
    }

    public function provideInvalidCredentials(): array
    {
        return [
            'Valid username, invalid password' => [
                'titouan.galopin@en-marche.fr',
                'foo-bar-pass',
            ],
            'Invalid username, valid password' => [
                'carl999@example.fr',
                'secret!12345',
            ],
            'Invalid username, invalid password' => [
                'carl999@example.fr',
                'foo-bar-pass',
            ],
        ];
    }
}
