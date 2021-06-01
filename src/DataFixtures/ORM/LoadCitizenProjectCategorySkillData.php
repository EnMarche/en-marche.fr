<?php

namespace App\DataFixtures\ORM;

use App\Entity\CitizenProjectCategorySkill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCitizenProjectCategorySkillData extends Fixture implements DependentFixtureInterface
{
    public const CATEGORY_SKILL_PROVIDER = [
            'cpc001' => [
                ['skill' => 'cps001', 'promotion' => true],
                ['skill' => 'cps002', 'promotion' => true],
                ['skill' => 'cps003', 'promotion' => true],
                ['skill' => 'cps004', 'promotion' => false],
                ['skill' => 'cps005', 'promotion' => false],
                ['skill' => 'cps006', 'promotion' => false],
            ],
            'cpc002' => [
                ['skill' => 'cps007', 'promotion' => true],
                ['skill' => 'cps008', 'promotion' => true],
                ['skill' => 'cps009', 'promotion' => true],
                ['skill' => 'cps010', 'promotion' => false],
                ['skill' => 'cps011', 'promotion' => false],
                ['skill' => 'cps012', 'promotion' => false],
            ],
            'cpc003' => [
                ['skill' => 'cps013', 'promotion' => true],
                ['skill' => 'cps014', 'promotion' => true],
                ['skill' => 'cps015', 'promotion' => true],
                ['skill' => 'cps016', 'promotion' => false],
            ],
            'cpc004' => [
                ['skill' => 'cps017', 'promotion' => true],
                ['skill' => 'cps018', 'promotion' => true],
                ['skill' => 'cps019', 'promotion' => true],
                ['skill' => 'cps020', 'promotion' => false],
            ],
            'cpc005' => [
                ['skill' => 'cps021', 'promotion' => true],
                ['skill' => 'cps022', 'promotion' => true],
                ['skill' => 'cps023', 'promotion' => true],
                ['skill' => 'cps024', 'promotion' => false],
            ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CATEGORY_SKILL_PROVIDER as $category => $items) {
            foreach ($items as $item) {
                $categorySkill = new CitizenProjectCategorySkill(
                    $this->getReference($category), $this->getReference($item['skill']), $item['promotion']
                );
                $manager->persist($categorySkill);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCitizenProjectSkillData::class,
            LoadCitizenProjectCategoryData::class,
        ];
    }
}
