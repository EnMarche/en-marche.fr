<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Repository\ReferentTagRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilData extends Fixture implements DependentFixtureInterface
{
    private const NAME_CORSE = 'Conseil territorial de la Corse';

    public function load(ObjectManager $manager)
    {
        // For french department tags
        foreach (range(1, 98) as $department) {
            $department = str_pad($department, 2, '0', \STR_PAD_LEFT);

            switch ($department) {
                // for Corsica
                case '20':
                    $this->createTerritorialCouncil($manager, self::NAME_CORSE, '20, 2A, 2B');

                    break;
                // for Paris
                case '75':
                    foreach (range(1, 20) as $district) {
                        $district = str_pad($district, 2, '0', \STR_PAD_LEFT);

                        $this->createTerritorialCouncil($manager, "Conseil territorial de Paris 750$district", "750$district");
                    }

                    $this->createTerritorialCouncil($manager, 'Conseil territorial de Paris', '75');

                    break;
                // does not exist
                case '96':
                    break;
                default:
                    $this->createTerritorialCouncil($manager, "Conseil territorial du département $department", $department);

                    break;
            }
        }

        $this->createTerritorialCouncil($manager, 'Conseil territorial des Français de l\'Étranger', ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG);

        $manager->flush();
    }

    private function createTerritorialCouncil(ObjectManager $manager, string $name, string $code): void
    {
        $territorialCouncil = new TerritorialCouncil($name, $code);

        if (self::NAME_CORSE === $name) {
            $territorialCouncil->addReferentTag($this->getReference('referent_tag_20'));
            $territorialCouncil->addReferentTag($this->getReference('referent_tag_2a'));
            $territorialCouncil->addReferentTag($this->getReference('referent_tag_2b'));
        } else {
            $territorialCouncil->addReferentTag($this->getReference('referent_tag_'.mb_strtolower($code)));
        }

        $manager->persist($territorialCouncil);

        $this->addReference('coTerr_'.mb_strtolower($code), $territorialCouncil);
    }

    public function getDependencies(): array
    {
        return [
            LoadReferentTagData::class,
        ];
    }
}
