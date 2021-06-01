<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Repository\ReferentTagRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadPoliticalCommitteeData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (range(1, 98) as $department) {
            $department = str_pad($department, 2, '0', \STR_PAD_LEFT);

            switch ($department) {
                // for Corsica
                case '20':
                    $this->createPoliticalCommittee($manager, 'CoPol de la Corse', '20, 2A, 2B');

                    break;
                // for Paris
                case '75':
                    foreach (range(1, 20) as $district) {
                        $district = str_pad($district, 2, '0', \STR_PAD_LEFT);

                        $this->createPoliticalCommittee($manager, "CoPol de Paris 750$district", "750$district");
                    }

                    $this->createPoliticalCommittee($manager, 'CoPol de Paris', '75', true);

                    break;
                case '77':
                    $this->createPoliticalCommittee($manager, "CoPol du département $department", $department, true);
                    break;
                // does not exist
                case '96':
                    break;
                default:
                    $this->createPoliticalCommittee($manager, "CoPol du département $department", $department);

                    break;
            }
        }

        $this->createPoliticalCommittee($manager, 'CoPol des Français de l\'Étranger', ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG);

        $manager->flush();
    }

    private function createPoliticalCommittee(
        ObjectManager $manager,
        string $name,
        string $code,
        bool $isActive = false
    ): void {
        $territorialCouncil = $this->getReference('coTerr_'.mb_strtolower($code));
        $politicalCommittee = new PoliticalCommittee(sprintf('%s (%s)', $name, $code), $territorialCouncil);
        $politicalCommittee->setIsActive($isActive);

        $manager->persist($politicalCommittee);

        $this->addReference('coPol_'.mb_strtolower($code), $politicalCommittee);
    }

    public function getDependencies()
    {
        return [
            LoadTerritorialCouncilData::class,
        ];
    }
}
