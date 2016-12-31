<?php

namespace AppBundle\Content;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class FilesystemAdapterFactory
{
    public static function createAdapter(string $environment, string $localPath, $gcloudId, $gcloudBucket)
    {
        if ($environment !== 'prod') {
            return new Local($localPath);
        }

        $storage = new StorageClient([
            'projectId' => $gcloudId,
            'keyFilePath' => '/app/gcloud-service-key.json'
        ]);

        return new CachedAdapter(
            new GoogleStorageAdapter($storage, $storage->bucket($gcloudBucket)),
            new Memory()
        );
    }
}
