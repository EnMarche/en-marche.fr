<?php

namespace App\DataFixtures\ORM;

use App\Entity\ReferentTag;
use App\Intl\UnitedNationsBundle;
use App\Repository\ReferentTagRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadReferentTagData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // French department tags
        foreach (range(1, 98) as $department) {
            $department = str_pad($department, 2, '0', \STR_PAD_LEFT);

            switch ($department) {
                // 2 separate tags for Corsica + 1 tag for whole Corsica
                case '20':
                    $this->createReferentTag($manager, 'Département 2A', '2A', ReferentTag::TYPE_DEPARTMENT);
                    $this->createReferentTag($manager, 'Département 2B', '2B', ReferentTag::TYPE_DEPARTMENT);
                    $this->createReferentTag($manager, 'Corse', '20');

                    break;
                // 1 tag for each Paris district + 1 tag for Paris
                case '75':
                    foreach (range(1, 20) as $district) {
                        $district = str_pad($district, 2, '0', \STR_PAD_LEFT);

                        $this->createReferentTag($manager, "Paris 750$district", "750$district");
                    }

                    $this->createReferentTag($manager, 'Paris', '75', ReferentTag::TYPE_DEPARTMENT);

                    break;
                // does not exist
                case '96':
                    break;
                default:
                    $this->createReferentTag($manager, "Département $department", $department, ReferentTag::TYPE_DEPARTMENT);

                    break;
            }
        }

        foreach (UnitedNationsBundle::getCountries() as $countryCode => $countryName) {
            $this->createReferentTag($manager, $countryName, $countryCode, ReferentTag::TYPE_COUNTRY);
        }

        $this->createReferentTag($manager, 'Français de l\'Étranger', ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG);
        $this->createReferentTag($manager, 'Métropole de Lyon (69M)', '69M', ReferentTag::TYPE_METROPOLIS);
        $this->createReferentTag($manager, 'Nouveau Rhône (69D)', '69D', ReferentTag::TYPE_DEPARTMENT);
        $this->createReferentTag($manager, 'Métropole de Montpellier (34M)', '34M', ReferentTag::TYPE_METROPOLIS);

        $manager->flush();
    }

    private function createReferentTag(ObjectManager $manager, string $name, string $code, string $type = null): void
    {
        $referentTag = new ReferentTag($name, $code);
        $referentTag->setType($type);

        $manager->persist($referentTag);

        $this->addReference('referent_tag_'.mb_strtolower($code), $referentTag);
    }
}
