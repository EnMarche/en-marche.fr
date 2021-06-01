<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Content\OrderArticleFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadOrderArticleData extends Fixture implements DependentFixtureInterface
{
    private $orderArticleFactory;
    private $mediaFactory;
    private $storage;

    public function __construct(
        OrderArticleFactory $orderArticleFactory,
        MediaFactory $mediaFactory,
        FilesystemInterface $storage
    ) {
        $this->orderArticleFactory = $orderArticleFactory;
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/guadeloupe.jpg');
        $this->storage->put('images/article.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Order article image', 'order_article.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        // Article
        $manager->persist($this->orderArticleFactory->createFromArray([
            'position' => 1,
            'title' => 'La première article sur la page des ordonnances',
            'slug' => 'premiere-article',
            'description' => 'La première article',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'sections' => [$this->getReference('os001'), $this->getReference('os004')],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->orderArticleFactory->createFromArray([
            'position' => 2,
            'title' => 'Article en brouillon pour la page des ordonnances en brouillon',
            'slug' => 'brouillon',
            'description' => 'Brouillon',
            'media' => $media,
            'displayMedia' => true,
            'published' => false,
            'publishedAt' => $faker->dateTimeThisDecade,
            'sections' => [$this->getReference('os001')],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadOrderSectionData::class];
    }
}
