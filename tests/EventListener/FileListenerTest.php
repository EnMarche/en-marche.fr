<?php

namespace Tests\App\EventListener;

use App\Entity\Mooc\AttachmentFile;
use App\EntityListener\FileListener;
use League\Flysystem\FilesystemInterface;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\App\AbstractKernelTestCase;

class FileListenerTest extends AbstractKernelTestCase
{
    private $entityFileListener;

    public function testProcessFile(): void
    {
        $entityFile = new AttachmentFile();
        $entityFile->setFile(new UploadedFile(__FILE__, __FILE__));

        $this->entityFileListener->processFile($entityFile);

        self::assertNotNull($entityFile->getPath());
        self::assertNull($entityFile->getFile());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFileListener = new FileListener(
            $this->get(FilesystemInterface::class),
            $this->get(Server::class)
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityFileListener = null;
    }
}
