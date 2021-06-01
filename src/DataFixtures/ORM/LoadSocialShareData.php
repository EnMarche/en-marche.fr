<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Entity\SocialShare;
use App\Entity\SocialShareCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadSocialShareData extends Fixture
{
    private $mediaFactory;
    private $storage;

    public function __construct(MediaFactory $mediaFactory, FilesystemInterface $storage)
    {
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        // Medias
        $medias = [];

        for ($i = 1; $i <= 4; ++$i) {
            $mediaFile = new File(__DIR__.'/../../../app/data/dist/social'.$i.'.jpg');
            $this->storage->put('images/social'.$i.'.jpg', file_get_contents($mediaFile->getPathname()));

            $manager->persist($media = $this->mediaFactory->createFromFile('Social image '.$i, 'social'.$i.'.jpg', $mediaFile));
            $medias[] = $media;
        }

        $manager->flush();

        $category1 = new SocialShareCategory('Culture', 1);
        $category2 = new SocialShareCategory('Défense', 2);
        $category3 = new SocialShareCategory('Santé', 3);

        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);

        $manager->flush();

        $socialShare1 = new SocialShare('Partage culture 1', 1, true);
        $socialShare1->setDefaultUrl('https://en-marche.fr/');
        $socialShare1->setDescription('description');
        $socialShare1->setFacebookUrl('https://www.facebook.com/EmmanuelMacron');
        $socialShare1->setMedia($medias[0]);
        $socialShare1->setSocialShareCategory($category1);

        $socialShare2 = new SocialShare('Fier d\'être Français', 2, true);
        $socialShare2->setDefaultUrl('https://en-marche.fr/');
        $socialShare2->setDescription('description');
        $socialShare2->setTwitterUrl('https://twitter.com/EmmanuelMacron');
        $socialShare2->setMedia($medias[1]);
        $socialShare2->setSocialShareCategory($category1);

        $socialShare3 = new SocialShare('Partage culture 3', 3, false);
        $socialShare3->setDefaultUrl('https://en-marche.fr/');
        $socialShare3->setDescription('description');
        $socialShare3->setMedia($medias[2]);
        $socialShare3->setSocialShareCategory($category1);

        $socialShare4 = new SocialShare('Partage Défense 1', 1, true);
        $socialShare4->setDefaultUrl('https://en-marche.fr/');
        $socialShare4->setDescription('description');
        $socialShare4->setMedia($medias[3]);
        $socialShare4->setSocialShareCategory($category2);

        $manager->persist($socialShare1);
        $manager->persist($socialShare2);
        $manager->persist($socialShare3);
        $manager->persist($socialShare4);

        $manager->flush();
    }
}
