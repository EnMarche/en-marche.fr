<?php

namespace App\DataFixtures\ORM;

use App\Content\CustomSearchResultFactory;
use App\Content\MediaFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadCustomSearchData extends Fixture
{
    private $customSearchResultFactory;
    private $mediaFactory;
    private $storage;

    public function __construct(
        CustomSearchResultFactory $customSearchResultFactory,
        MediaFactory $mediaFactory,
        FilesystemInterface $storage
    ) {
        $this->customSearchResultFactory = $customSearchResultFactory;
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        $description = 'Pour ceux qui sont convaincus que le pays est bloqué, qui ont le goût du travail, du progrès, '.
            'du risque, qui vivent pour la liberté, l\'égalité, et l\'Europe.';

        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $this->storage->put('images/custom_search.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Custom search image', 'custom_search.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        $manager->persist($this->customSearchResultFactory->createFromArray([
            'keywords' => 'programme propositions',
            'title' => 'Le programme d\'Emmanuel Macron',
            'url' => '/emmanuel-macron/le-programme',
            'description' => 'Le moment que nous vivons est celui d’une refondation profonde de la France. Voici celle que nous vous proposons.',
            'media' => $media,
        ]));

        $manager->persist($this->customSearchResultFactory->createFromArray([
            'keywords' => 'mouvement en marche nos valeurs',
            'title' => 'Le mouvement - Nos valeurs',
            'url' => '/le-mouvement',
            'description' => $description,
            'media' => $media,
        ]));

        $manager->flush();
    }
}
