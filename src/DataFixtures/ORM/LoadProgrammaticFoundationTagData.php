<?php

namespace App\DataFixtures\ORM;

use App\Entity\ProgrammaticFoundation\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadProgrammaticFoundationTagData extends Fixture
{
    private const TAG_LABELS = [
        'Écologie',
        'Santé',
        'Économie',
        'Loisir',
        'Avenir',
        'Jeunesse',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::TAG_LABELS as $key => $label) {
            $tag = new Tag($label);
            $manager->persist($tag);
            $this->addReference("programmatic-foundation-tag-$key", $tag);
        }

        $manager->flush();
    }
}
