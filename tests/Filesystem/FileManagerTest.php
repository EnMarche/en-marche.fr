<?php

namespace Tests\App\Filesystem;

use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FileTypeEnum;
use App\Filesystem\FileManager;
use App\Repository\Filesystem\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerTest extends TestCase
{
    private $storage;
    private $entityManager;
    private $repository;
    private $fileManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->createMock(FilesystemInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(FileRepository::class);

        $this->fileManager = new FileManager(
            $this->storage,
            $this->entityManager,
            $this->repository
        );
    }

    protected function tearDown(): void
    {
        $this->fileManager = null;
        $this->storage = null;
        $this->entityManager = null;
        $this->repository = null;

        parent::tearDown();
    }

    public function testUpdateIfExternalLink(): void
    {
        $link = 'https://test.com';
        $file = new File();
        $file->setExternalLink($link);
        $file->setType('file');
        $file->setOriginalFilename('my.jpg');
        $file->setExtension('jpg');
        $file->setMimeType('image/jpeg');
        $file->setSize(54321);

        $this->fileManager->update($file);

        $this->assertSame(FileTypeEnum::EXTERNAL_LINK, $file->getType());
        $this->assertSame($link, $file->getExternalLink());
        $this->assertNull($file->getOriginalFilename());
        $this->assertNull($file->getExtension());
        $this->assertNull($file->getMimeType());
        $this->assertNull($file->getSize());
    }

    public function testUpdateIfFile(): void
    {
        $file = new File();
        $file->setExternalLink(null);
        $file->setType('external_link');
        $file->setOriginalFilename(null);
        $file->setExtension(null);
        $file->setMimeType(null);
        $file->setSize(null);
        $uploadedFile = new UploadedFile(__DIR__.'/../Fixtures/image.jpg', 'image.jpg', 'image/jpeg', \UPLOAD_ERR_OK, true);
        $file->setFile($uploadedFile);

        $this->fileManager->update($file);

        $this->assertSame(FileTypeEnum::FILE, $file->getType());
        $this->assertNull($file->getExternalLink());
        $this->assertSame('image.jpg', $file->getOriginalFilename());
        $this->assertSame('jpg', $file->getExtension());
        $this->assertSame('image/jpeg', $file->getMimeType());
        $this->assertSame(285, $file->getSize());
    }
}
