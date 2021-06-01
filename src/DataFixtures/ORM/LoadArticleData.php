<?php

namespace App\DataFixtures\ORM;

use App\Content\ArticleFactory;
use App\Content\MediaFactory;
use App\Entity\ArticleCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadArticleData extends Fixture
{
    private $articleFactory;
    private $mediaFactory;
    private $storage;

    public function __construct(
        ArticleFactory $articleFactory,
        MediaFactory $mediaFactory,
        FilesystemInterface $storage
    ) {
        $this->articleFactory = $articleFactory;
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/guadeloupe.jpg');
        $this->storage->put('images/article.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Article image', 'article.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        // Categories
        $manager->persist($newsCategory = new ArticleCategory('Actualités', 'actualites', 1));
        $manager->persist($videosCategory = new ArticleCategory('Vidéos', 'videos', 2));
        $manager->persist($speechCategory = new ArticleCategory('Discours', 'discours', 3));
        $manager->persist($mediasCategory = new ArticleCategory('Médias', 'medias', 4));
        $manager->persist($communiquesCategory = new ArticleCategory('Communiqués', 'communiques', 5));
        $manager->persist($opinionsCategory = new ArticleCategory('Opinions', 'opinions', 6, 'http://www.google.fr', 'Google link'));
        $manager->persist($noDisplayCategory = new ArticleCategory('Not displayed', 'nodisplay', 7, null, null, false));

        $manager->flush();

        // Article
        $manager->persist($this->articleFactory->createFromArray([
            'title' => '« Les outre-mer sont l’un des piliers de notre richesse culturelle. »',
            'slug' => 'outre-mer',
            'description' => 'outre-mer',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $newsCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->articleFactory->createFromArray([
            'title' => '« Mes opinions »',
            'slug' => 'mes-opinions',
            'description' => 'mes-opinions',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $opinionsCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->articleFactory->createFromArray([
            'title' => 'Article en brouillon',
            'slug' => 'brouillon',
            'description' => 'brouillon',
            'media' => $media,
            'displayMedia' => true,
            'published' => false,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $newsCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->articleFactory->createFromArray([
            'title' => 'Article image cachée',
            'slug' => 'sans-image',
            'description' => 'sans-image',
            'media' => $media,
            'displayMedia' => false,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $speechCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->articleFactory->createFromArray([
            'title' => 'Article dans une category pas affichée',
            'slug' => 'article-avec-category-non-afficher',
            'description' => 'Article dans une category pas affichée',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'publishedAt' => $faker->dateTimeThisDecade,
            'category' => $noDisplayCategory,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        // A lot of articles for listing
        foreach ([$newsCategory, $videosCategory, $speechCategory, $mediasCategory, $communiquesCategory, $opinionsCategory, $noDisplayCategory] as $category) {
            for ($i = 0; $i < 25; ++$i) {
                $manager->persist($this->articleFactory->createFromArray([
                    'title' => $title = mb_substr($faker->sentence(), 0, 60),
                    'slug' => Urlizer::urlize($title),
                    'description' => $faker->text(),
                    'media' => $media,
                    'displayMedia' => false,
                    'published' => true,
                    'publishedAt' => $faker->dateTimeThisDecade,
                    'category' => $category,
                    'content' => file_get_contents(__DIR__.'/../content.md'),
                    'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
                ]));
            }
        }

        $manager->flush();
    }
}
