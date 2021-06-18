<?php

namespace Tests\App;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    use TestHelperTrait;

    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->manager = $this->getEntityManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        static::ensureKernelShutdown();
        static::$kernel = null;
        static::$booted = false;

        $this->manager = null;
    }
}
