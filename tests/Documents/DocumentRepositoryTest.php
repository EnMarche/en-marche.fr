<?php

namespace Tests\App\Documents;

use App\Documents\DocumentRepository;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\TestCase;

class DocumentRepositoryTest extends TestCase
{
    /** @var Filesystem */
    private $filesystem;

    /** @var DocumentRepository */
    private $repository;

    public function testListDocuments()
    {
        $this->put(DocumentRepository::DIRECTORY_ADHERENTS, 'documenta.pdf', 'Document A');
        $this->createDir(DocumentRepository::DIRECTORY_ADHERENTS, 'mydir');
        $this->createDir(DocumentRepository::DIRECTORY_ADHERENTS, 'mydir/subdir');
        $this->put(DocumentRepository::DIRECTORY_ADHERENTS, 'mydir/documentb.pdf', 'Document B');
        $this->put(DocumentRepository::DIRECTORY_ADHERENTS, 'mydir/subdir/documentc.pdf', 'Document C');

        $this->assertCount(0, $this->repository->listHostDirectory());
        $this->assertCount(0, $this->repository->listReferentDirectory());

        $adherentsRoot = $this->repository->listDirectory(DocumentRepository::DIRECTORY_ADHERENTS, '/');
        $this->assertCount(2, $adherentsRoot);
        $this->assertEquals($adherentsRoot, $this->repository->listAdherentDirectory());
        $this->assertSame('documenta.pdf', $adherentsRoot[0]->getName());
        $this->assertSame('mydir', $adherentsRoot[1]->getName());

        $adherentsMydir = $this->repository->listDirectory(DocumentRepository::DIRECTORY_ADHERENTS, '/mydir');
        $this->assertCount(2, $adherentsMydir);
        $this->assertSame('documentb.pdf', $adherentsMydir[0]->getName());
        $this->assertSame('subdir', $adherentsMydir[1]->getName());
    }

    public function testReadDocument()
    {
        $fixture = file_get_contents(__DIR__.'/../Fixtures/document.pdf');
        $this->put(DocumentRepository::DIRECTORY_ADHERENTS, 'document.pdf', $fixture);

        $document = $this->repository->readDocument(DocumentRepository::DIRECTORY_ADHERENTS, 'document.pdf');
        $this->assertArrayHasKey('mimetype', $document);
        $this->assertArrayHasKey('content', $document);
        $this->assertSame('application/pdf', $document['mimetype']);
        $this->assertSame($fixture, $document['content']);
    }

    public function testReadDocumentFailsWhenInvalid()
    {
        $this->expectException(FileNotFoundException::class);
        $this->repository->readDocument(DocumentRepository::DIRECTORY_ADHERENTS, 'invalid.pdf');
    }

    private function createDir(string $directory, string $path)
    {
        $this->filesystem->createDir(DocumentRepository::DIRECTORY_ROOT.'/'.$directory.'/'.$path);
    }

    private function put(string $directory, string $path, string $content)
    {
        $this->filesystem->write(DocumentRepository::DIRECTORY_ROOT.'/'.$directory.'/'.$path, $content);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem(new MemoryAdapter());
        $this->repository = new DocumentRepository($this->filesystem);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->filesystem = null;
        $this->repository = null;
    }
}
