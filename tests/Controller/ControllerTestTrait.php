<?php

namespace Tests\App\Controller;

use App\Entity\Adherent;
use App\Entity\Event\InstitutionalEventCategory;
use App\Entity\ReferentTag;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Tests\App\TestHelperTrait;

/**
 * @method static assertSame($expected, $actual, $message = '')
 */
trait ControllerTestTrait
{
    use TestHelperTrait;

    public function assertResponseStatusCode(int $statusCode, Response $response, string $message = '')
    {
        $this->assertSame($statusCode, $response->getStatusCode(), $message);
    }

    public function assertClientIsRedirectedTo(
        string $path,
        KernelBrowser $client,
        bool $withSchemes = false,
        bool $permanent = false
    ): void {
        $response = $client->getResponse();

        $this->assertResponseStatusCode($permanent ? Response::HTTP_MOVED_PERMANENTLY : Response::HTTP_FOUND, $response);
        $this->assertSame(
            $withSchemes ? $client->getRequest()->getSchemeAndHttpHost().$path : $path,
            $response->headers->get('location')
        );
    }

    public function assertStatusCode(int $code, KernelBrowser $client): void
    {
        $this->assertResponseStatusCode($code, $client->getResponse());
    }

    public function isSuccessful(Response $response): void
    {
        static::assertTrue($response->isSuccessful());
    }

    public function logout(KernelBrowser $client): void
    {
        $session = $client->getContainer()->get('session');

        $client->getCookieJar()->clear();
        $session->set('_security_main_context', null);
        $session->save();
    }

    public function authenticateAsAdherent(KernelBrowser $client, string $emailAddress): void
    {
        if (!$user = $this->getAdherentRepository()->findOneBy(['emailAddress' => $emailAddress])) {
            throw new \Exception(sprintf('Adherent %s not found', $emailAddress));
        }

        $this->authenticate($client, $user, 'main');
    }

    public function authenticateAsAdmin(KernelBrowser $client, string $email = 'admin@en-marche-dev.fr'): void
    {
        if (!$user = $this->getAdministratorRepository()->loadUserByUsername($email)) {
            throw new \Exception(sprintf('Admin %s not found', $email));
        }

        $this->authenticate($client, $user, 'admin');
    }

    protected function authenticateAsAdherentWithChoosingSpace(string $email, string $spaceLinkName): void
    {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request(Request::METHOD_GET, '/');

        self::assertStringContainsString($spaceLinkName, $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
        $this->client->click($crawler->selectLink($spaceLinkName)->link());

        $path = '';
        if (0 == strpos($spaceLinkName, 'Espace candidat')) {
            $path = '/espace-candidat/utilisateurs';
        }
        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($path, $this->client);
        $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
    }

    protected function getFirstPrefixForm(Form $form): ?string
    {
        foreach ($form->all() as $field) {
            preg_match('/^(.*)\[.*\]$/', $field->getName(), $matches);
            if ($matches) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function seeFlashMessage(Crawler $crawler, ?string $message = null, string $level = 'info'): bool
    {
        $flash = $crawler->filter('.flash--'.$level);

        if ($message) {
            self::assertSame($message, trim($flash->text()));
        }

        return 1 === \count($flash);
    }

    protected function appendCollectionFormPrototype(
        \DOMElement $collection,
        string $newIndex = '0',
        string $prototypeName = '__name__'
    ): void {
        $prototypeHTML = $collection->getAttribute('data-prototype');
        $prototypeHTML = str_replace($prototypeName, $newIndex, $prototypeHTML);
        $prototypeFragment = new \DOMDocument();
        $prototypeFragment->loadHTML($prototypeHTML);
        foreach ($prototypeFragment->getElementsByTagName('body')->item(0)->childNodes as $prototypeNode) {
            $collection->appendChild($collection->ownerDocument->importNode($prototypeNode, true));
        }
    }

    protected function assertSeeCommitteeTimelineMessage(
        Crawler $crawler,
        int $position,
        string $author,
        string $role,
        string $text
    ) {
        $message = $crawler->filter('.committee__timeline__message')->eq($position);

        $this->assertStringContainsString($author, $message->filter('h3')->text());
        $this->assertSame($role, $message->filter('h3 span')->text());
        $this->assertStringContainsString($text, $message->filter('div')->first()->text());
    }

    protected function assertHavePublishedMessage(string $queue, string $msgBody): void
    {
        $messages = array_filter(
            $this->getMessages($queue),
            function ($message) use ($msgBody) { return $msgBody === $message->getBody(); }
        );

        self::assertEquals(1, \count($messages), 'Expected message not found.');
    }

    private function getMessages(string $queue): array
    {
        $channel = $this->get('old_sound_rabbit_mq.connection.default')->channel();
        $messages = [];

        /** @var AMQPMessage $message */
        while ($message = $channel->basic_get($queue)) {
            $messages[] = $message;
            $channel->basic_ack($message->get('delivery_tag'));
        }

        return $messages;
    }

    private function authenticate(KernelBrowser $client, UserInterface $user, string $firewallName): void
    {
        $session = $client->getContainer()->get('session');

        $token = new PostAuthenticationGuardToken($user, $firewallName, $user->getRoles());
        $session->set('_security_main_context', serialize($token));
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    private function getInstitutionalEventCategoryIdByName(string $categoryName): int
    {
        return $this->manager->getRepository(InstitutionalEventCategory::class)
            ->findOneBy(['name' => $categoryName])->getId()
        ;
    }

    private static function assertAdherentHasReferentTag(Adherent $adherent, string $code): void
    {
        $referentTag = $adherent
            ->getReferentTags()
            ->filter(function (ReferentTag $referentTag) use ($code) {
                return $code === $referentTag->getCode();
            })
            ->first()
        ;

        self::assertInstanceOf(ReferentTag::class, $referentTag);
        self::assertSame($referentTag->getCode(), $code);
    }

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return $this->client->getContainer()->get(MessageRecorderInterface::class);
    }

    protected function getUrl(
        string $route,
        array $params = [],
        int $absolute = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->get('router')->generate($route, $params, $absolute);
    }
}
