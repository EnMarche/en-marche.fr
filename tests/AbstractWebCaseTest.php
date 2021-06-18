<?php

namespace Tests\App;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebCaseTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;
    /** @var EntityManagerInterface */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->setServerParameter('HTTP_HOST', self::$container->getParameter('app_host'));

        $this->manager = self::$container->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        static::ensureKernelShutdown();
        static::$kernel = null;
        static::$booted = false;

        $this->client = null;
        $this->manager = null;
    }
}
