<?php

namespace App\DataFixtures\ORM;

use App\Entity\FacebookVideo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LoadFacebookVideoData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $video = new FacebookVideo();
        $video->setFacebookUrl('https://www.facebook.com/EnMarche/videos/771225236389014/');
        $video->setTwitterUrl('https://twitter.com/enmarchefr/status/852951098142990338');
        $video->setDescription('#MacronPau avec les helpers en coulisses. Allez allez ! Cette révolution nous allons la porter.');
        $video->setAuthor('En Marche');
        $video->setPosition(2);
        $video->setPublished(true);
        $manager->persist($video);

        $video = new FacebookVideo();
        $video->setFacebookUrl('https://www.facebook.com/EnMarche/videos/769283806583157/');
        $video->setDescription('Laurence Haïm a un message pour vous. Inscrivez-vous ➜ en-marche.fr/bercy');
        $video->setAuthor('Laurence Haïm');
        $video->setPosition(1);
        $video->setPublished(true);
        $manager->persist($video);

        $video = new FacebookVideo();
        $video->setFacebookUrl('https://www.facebook.com/EnMarche/videos/1946206882278555/');
        $video->setDescription('J-5 avant le grand meeting d\'Emmanuel Macron à Paris-Bercy ! Découvrez le teaser');
        $video->setAuthor('Emmanuel Macron');
        $video->setPosition(3);
        $video->setPublished(false);
        $manager->persist($video);

        $faker = Factory::create('fr_FR');

        // Some videos for display
        for ($i = 0; $i < 10; ++$i) {
            $video = new FacebookVideo();
            $video->setFacebookUrl('https://www.facebook.com/EnMarche/videos/1946206882278555/');
            $video->setDescription($faker->sentence());
            $video->setAuthor($faker->name);
            $video->setPosition($i + 4);
            $video->setPublished(true);
            $manager->persist($video);
        }

        $manager->flush();
    }
}
