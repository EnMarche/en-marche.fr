<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Entity\Formation\Axe;
use App\Entity\Formation\Module;
use App\Entity\Formation\Path;
use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadFormationData extends Fixture
{
    private $faker;
    private $mediaFactory;
    private $storage;

    public function __construct(MediaFactory $mediaFactory, FilesystemInterface $storage)
    {
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $this->storage->put('images/espace-formation.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Image pour l\'espace formation', 'espace-formation.jpg', $mediaFile);

        $manager->persist($path1 = $this->createPath('Parcours 1'));
        $manager->persist($path2 = $this->createPath('Parcours 2'));

        $manager->persist($axe1 = $this->createAxe($path1, 'Premier axe', $media));
        $manager->persist($axe2 = $this->createAxe($path1, 'Deuxième axe', $media));
        $manager->persist($axe3 = $this->createAxe($path2, 'Premier axe', $media));
        $manager->persist($axe4 = $this->createAxe($path2, 'Deuxième axe', $media));

        $manager->persist($this->createModule($axe1, 'Premier article du premier axe', $media));
        $manager->persist($this->createModule($axe1, 'Deuxième article du premier axe', $media));
        $manager->persist($this->createModule($axe1, 'Troisième article du premier axe', $media));

        $manager->persist($this->createModule($axe2, 'Premier article du deuxième axe', $media));
        $manager->persist($this->createModule($axe2, 'Deuxième article du deuxième axe', $media));
        $manager->persist($this->createModule($axe2, 'Troisième article du deuxième axe', $media));

        $manager->persist($this->createModule($axe3, 'Premier article du troisième axe', $media));
        $manager->persist($this->createModule($axe3, 'Deuxième article du troisième axe', $media));
        $manager->persist($this->createModule($axe3, 'Troisième article du troisième axe', $media));

        $manager->persist($this->createModule($axe4, 'Premier article du quatrième axe', $media));
        $manager->persist($this->createModule($axe4, 'Deuxième article du quatrième axe', $media));
        $manager->persist($this->createModule($axe4, 'Troisième article du quatrième axe', $media));

        $manager->flush();
    }

    private function createAxe(Path $path, string $title, Media $media): Axe
    {
        $axe = new Axe();
        $axe->setPath($path);
        $axe->setTitle($title);
        $axe->setDescription($this->faker->text('200'));
        $axe->setMedia($media);
        $axe->setDisplayMedia(true);
        $axe->setContent(file_get_contents(__DIR__.'/../content.md'));

        return $axe;
    }

    private function createModule(Axe $axe, string $title, Media $media): Module
    {
        $module = new Module();
        $module->setAxe($axe);
        $module->setTitle($title);
        $module->setDescription($this->faker->text(200));
        $module->setMedia($media);
        $module->setDisplayMedia(true);
        $module->setContent(file_get_contents(__DIR__.'/../content.md'));

        return $module;
    }

    private function createPath(string $title): Path
    {
        $path = new Path();
        $path->setTitle($title);
        $path->setDescription(<<<EOT
    Découvrez maintenant votre parcours personnalisé.
    Les modules sont numérotés pour vous permettre de
    compléter / renforcer vos compétences par ordre de priorité.
EOT
        );

        return $path;
    }
}
