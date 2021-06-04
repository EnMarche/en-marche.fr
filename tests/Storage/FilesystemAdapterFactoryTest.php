<?php

namespace Tests\App\Storage;

use App\Storage\FilesystemAdapterFactory;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use PHPUnit\Framework\TestCase;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilesystemAdapterFactoryTest extends TestCase
{
    public function testCreateDevAdapter()
    {
        $tmp = sys_get_temp_dir().'/en-marche-filesystem-local';

        $adapter = FilesystemAdapterFactory::createAdapter('dev', $tmp, null, null, null, $this->createMock(UrlGeneratorInterface::class));

        $this->assertInstanceOf(Local::class, $adapter);
        $this->assertFileExists($tmp);

        rmdir($tmp);
    }

    public function testCreateProdAdapter()
    {
        $adapter = FilesystemAdapterFactory::createAdapter('prod', '', 'project-bucket', $this->createMock(UrlGeneratorInterface::class));

        $this->assertInstanceOf(CachedAdapter::class, $adapter);
        $this->assertInstanceOf(GoogleStorageAdapter::class, $adapter->getAdapter());
    }
}
