<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group membership
 */
class UserControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testUserCannotReplaceYourEmailByOneAlreadyUsed(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/modifier-email');

        $crawler = $this->client->submit($crawler->selectButton('Modifier')->form(), [
            'adherent_change_email[email]' => 'referent@en-marche-dev.fr',
        ]);

        $errors = $crawler->filter('.form__errors > li');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertSame('Cette adresse e-mail existe déjà.', $errors->eq(0)->text());
    }

    public function testUserCanValidateYourNewEmail(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request('GET', '/parametres/mon-compte/modifier-email');

        $this->client->submit($crawler->selectButton('Modifier')->form(), [
            'adherent_change_email[email]' => 'new.mail@test.com',
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/modifier', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(200, $this->client);

        $this->seeFlashMessage($crawler, 'Nous avons envoyé un e-mail à new.mail@test.com pour vérifier votre adresse e-mail. Cliquez sur le lien qui y est présent pour valider le changement.');

        $token = $this->getRepository(AdherentChangeEmailToken::class)->findLastUnusedByEmail('new.mail@test.com');

        $this->client->request(Request::METHOD_GET, sprintf('/valider-changement-email/%s/%s', $token->getAdherentUuid(), $token->getValue()));
        $this->assertClientIsRedirectedTo('/', $this->client);

        $flash = $this->client->getRequest()->getSession()->getFlashBag()->get('info');
        self::assertCount(1, $flash);
        self::assertSame('adherent.change_email.success', current($flash));

        $this->assertHavePublishedMessage('api_sync', '{"uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458","subscriptionExternalIds":["123abc","456def"],"city":"Mouxy","country":"FR","zipCode":"73100","tags":["73","CIRCO_73004"],"emailAddress":"new.mail@test.com","firstName":"Carl","lastName":"Mirabeau"}');

        $this->manager->clear(Adherent::class);
        $adherent = $this->getAdherentRepository()->findOneByUuid($token->getAdherentUuid()->toString());
        self::assertSame('new.mail@test.com', $adherent->getEmailAddress());
    }
}
