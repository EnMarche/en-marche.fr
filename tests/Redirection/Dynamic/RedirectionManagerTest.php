<?php

namespace Tests\App\Redirection\Dynamic;

use App\Entity\Redirection;
use App\Redirection\Dynamic\RedirectionManager;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class RedirectionManagerTest extends AbstractKernelTestCase
{
    public function testSimpleResolveRedirection(): void
    {
        $redirectionManager = $this->get(RedirectionManager::class);

        self::assertNull($redirectionManager->getRedirection('/test1'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test1', '/test2'));
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test1'));
        self::assertRedirectionValues($redirection, '/test1', '/test2');
    }

    public function testMultipleResolveRedirection(): void
    {
        $redirectionManager = $this->get(RedirectionManager::class);

        self::assertNull($redirectionManager->getRedirection('/test1'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test1', '/test2'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test2', '/test3'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test3', '/test4'));
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test1'));
        self::assertRedirectionValues($redirection, '/test1', '/test4');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test2'));
        self::assertRedirectionValues($redirection, '/test2', '/test4');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test3'));
        self::assertRedirectionValues($redirection, '/test3', '/test4');
    }

    public function testLoopRedirection(): void
    {
        $redirectionManager = $this->get(RedirectionManager::class);

        self::assertNull($redirectionManager->getRedirection('/test1'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test1', '/test2'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test2', '/test3'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test3', '/test4'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test4', '/test1'));
        self::assertNull($redirectionManager->getRedirection('/test1'));
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test2'));
        self::assertRedirectionValues($redirection, '/test2', '/test1');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test3'));
        self::assertRedirectionValues($redirection, '/test3', '/test1');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test4'));
        self::assertRedirectionValues($redirection, '/test4', '/test1');
    }

    private static function assertRedirectionValues(
        Redirection $redirection,
        string $source,
        string $target,
        int $type = 301
    ): void {
        self::assertSame($source, $redirection->getFrom());
        self::assertSame($target, $redirection->getTo());
        self::assertSame($type, $redirection->getType());
    }
}
