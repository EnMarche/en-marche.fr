<?php

namespace Tests\App\OAuth;

use App\Entity\OAuth\Client;
use App\OAuth\CallbackManager;
use App\Repository\OAuth\ClientRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CallbackManagerTest extends TestCase
{
    private const KNOWN_CLIENT_UUID = 'ae65d178-3dc6-4c14-843c-36df38c82825';
    private const UNKNOWN_CLIENT_UUID = 'ae65d178-3dc6-4c14-843c-36df38c82824';
    /**
     * @var CallbackManager
     */
    private $callbackManager;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var MockObject|ClientRepository
     */
    private $clientRepository;

    /**
     * @var MockObject|UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->clientRepository->expects($this->any())
            ->method('findClientByUuid')
            ->willReturnCallback(function (UuidInterface $uuid) {
                return self::KNOWN_CLIENT_UUID === $uuid->toString() ? $this->createClient() : null;
            })
        ;

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->request = new Request();
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())
            ->method('getMasterRequest')
            ->willReturnCallback(function () {
                return $this->request;
            })
        ;

        $this->callbackManager = new CallbackManager($this->urlGenerator, $requestStack, $this->clientRepository, $this->logger);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->logger = null;
        $this->clientRepository = null;
        $this->urlGenerator = null;
        $this->request = null;
        $this->callbackManager = null;
    }

    public function testItGeneratesUrlWithCallbackInformation(): void
    {
        $this->request->query->set('redirect_uri', $redirectUri = 'https://enmarche.fr');
        $this->request->query->set('client_id', $clientId = self::KNOWN_CLIENT_UUID);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('foo', ['test' => 'foo', 'client_id' => $clientId, 'redirect_uri' => $redirectUri], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/foo_path')
        ;

        $this->logger->expects($this->never())->method($this->anything());

        $this->assertSame('/foo_path', $this->callbackManager->generateUrl('foo', ['test' => 'foo']));
        $this->assertSame('https://enmarche.fr', $this->callbackManager->redirectToClientIfValid('foo')->getTargetUrl());
    }

    /**
     * @dataProvider providesInvalidClientOrRedirectUri
     */
    public function testItGeneratesUrlWithCallbackInformationWhenRedirectUriOrClientIdIsNotValid(
        string $redirectUri,
        string $clientId
    ): void {
        $this->request->query->set('redirect_uri', $redirectUri);
        $this->request->query->set('client_id', $clientId);

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->with('foo', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/foo_path')
        ;

        $this->logger->expects($this->exactly(2))->method('warning');

        $this->assertSame('/foo_path', $this->callbackManager->generateUrl('foo'));
        $this->assertSame('/foo_path', $this->callbackManager->redirectToClientIfValid('foo')->getTargetUrl());
    }

    public function providesInvalidClientOrRedirectUri(): array
    {
        return [
            'Redirect Uri Not supported by client' => ['https://enmarche.fr/NotSupported', self::KNOWN_CLIENT_UUID],
            'Client does not exist' => ['https://enmarche.fr', self::UNKNOWN_CLIENT_UUID],
            'Malformed UUID' => ['https://enmarche.fr', 'ansuite"*«»-'],
        ];
    }

    public function testItGeneratesUrlWithoutCallbackInformationWhenNothingIsProvided(): void
    {
        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->with('foo', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/foo_path')
        ;

        $this->clientRepository->expects($this->never())->method($this->anything());

        $this->assertSame('/foo_path', $this->callbackManager->generateUrl('foo'));
        $this->assertSame('/foo_path', $this->callbackManager->redirectToClientIfValid('foo')->getTargetUrl());
    }

    public function testItGeneratesUrlWithoutCallbackInformationWhenClientIdIsMissing(): void
    {
        $this->request->query->set('redirect_uri', 'https://enmarche.fr');

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->with('foo', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/foo_path')
        ;

        $this->clientRepository->expects($this->never())->method($this->anything());

        $this->assertSame('/foo_path', $this->callbackManager->generateUrl('foo'));
        $this->assertSame('/foo_path', $this->callbackManager->redirectToClientIfValid('foo')->getTargetUrl());
    }

    public function testItGeneratesUrlWithoutCallbackInformationWhenRedirectUriIsMissing(): void
    {
        $this->request->query->set('client_id', self::KNOWN_CLIENT_UUID);

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->with('foo', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/foo_path')
        ;

        $this->clientRepository->expects($this->never())->method($this->anything());

        $this->assertSame('/foo_path', $this->callbackManager->generateUrl('foo'));
        $this->assertSame('/foo_path', $this->callbackManager->redirectToClientIfValid('foo')->getTargetUrl());
    }

    private function createClient(): Client
    {
        return new Client(null, 'test', 'description', 'secret', [], ['https://enmarche.fr']);
    }
}
