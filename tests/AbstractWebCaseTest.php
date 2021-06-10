<?php

namespace Tests\App;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractWebCaseTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient(['HTTP_HOST' => 'test.enmarche.code']);
    }

    /**
     * @var KernelInterface
     */
    private static $localKernel;

    protected function tearDown(): void
    {
        $this->client = null;

        if (self::$localKernel !== null) {
            self::$localKernel->shutdown();
        }

        parent::tearDown();
    }

    /**
     * override from Liip\FunctionalTestBundle\Test\WebTestCase to allow garbage collection
     */
    protected function getContainer(): ContainerInterface
    {
        if (self::$localKernel === null) {
            self::$localKernel = self::createKernel();
        }

        self::$localKernel->boot();

        return self::$localKernel->getContainer()->get('test.service_container');
    }
}
