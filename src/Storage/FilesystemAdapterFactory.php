<?php

namespace App\Storage;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilesystemAdapterFactory
{
    public static function createAdapter(
        string $environment,
        string $localPath,
        $gcloudBucket,
        UrlGeneratorInterface $urlGenerator
    ) {
        if ('prod' !== $environment) {
            $adapter = new WebLocalAdapter($localPath);
            $adapter->setUrlGenerator($urlGenerator);

            return $adapter;
        }

        return new CachedAdapter(
            new GoogleStorageAdapter($storage = new StorageClient(), $storage->bucket($gcloudBucket)),
            new Memory()
        );
    }
}
