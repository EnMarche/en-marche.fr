<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\IdeasWorkshop\AuthorCategoryEnum;
use App\Entity\IdeasWorkshop\Idea;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadIdeaData extends Fixture implements DependentFixtureInterface
{
    public const IDEA_01_UUID = 'e4ac3efc-b539-40ac-9417-b60df432bdc5';
    public const IDEA_02_UUID = '3b1ea810-115f-4b2c-944d-34a55d7b7e4d';
    public const IDEA_03_UUID = 'aa093ce6-8b20-4d86-bfbc-91a73fe47285';
    public const IDEA_04_UUID = 'c14937d6-fd42-465c-8419-ced37f3e6194';
    public const IDEA_05_UUID = '9529e98c-2524-486f-a6ed-e2d707dc99ea';
    public const IDEA_06_UUID = 'bbf35ba6-52ba-4913-aae8-5948449d0c1d';
    public const IDEA_07_UUID = '982bd810-a3ef-4611-a998-ebfadc335d66';
    public const IDEA_08_UUID = '6b6e41f9-d14b-4c86-9328-11151c99fc84';

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_idea');

        $need = $this->getReference('need-legal');
        $category = $this->getReference('category-european');
        $themeDefense = $this->getReference('theme-army-defense');
        $themeEcology = $this->getReference('theme-ecology');
        $committee = $this->getReference('committee-1');
        $adherent3 = $this->getReference('adherent-3');
        $adherent6 = $this->getReference('adherent-6');
        $adherent13 = $this->getReference('adherent-13');

        $ideaMakePeace = new Idea(
            'Faire la paix',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.',
            AuthorCategoryEnum::COMMITTEE,
            new \DateTime('-3 days 1 minute'),
            new \DateTime('+13 days 1 minute'),
            true,
            $adherent3,
            Uuid::fromString(self::IDEA_01_UUID),
            new \DateTime('-20 days 1 minute')
        );
        $ideaMakePeace->setCategory($category);
        $ideaMakePeace->setCommittee($committee);
        $ideaMakePeace->addTheme($themeDefense);
        $ideaMakePeace->addNeed($need);
        $this->addReference('idea-peace', $ideaMakePeace);

        $ideaHelpEcology = new Idea(
            'Favoriser l\'écologie',
            'Mauris posuere eros eget nunc dapibus ornare. Vestibulum dolor eros, facilisis in venenatis eu, tristique a sapien.',
            AuthorCategoryEnum::COMMITTEE,
            null,
            null,
            true,
            $adherent3,
            Uuid::fromString(self::IDEA_02_UUID),
            new \DateTime('-15 days 1 minute')
        );
        $ideaHelpEcology->setCategory($category);
        $ideaHelpEcology->setCommittee($committee);
        $ideaHelpEcology->addTheme($themeEcology);
        $this->addReference('idea-help-ecology', $ideaHelpEcology);

        $ideaHelpPeople = new Idea(
            'Aider les gens ?!“”‘’‛‟.,‚„"^°…:;&#_¯–‑—@%‰/\¨`+-$*<>×÷()€«»~',
            'Nam laoreet eros diam, vitae hendrerit libero interdum nec. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
            AuthorCategoryEnum::QG,
            null,
            null,
            true,
            $adherent6,
            Uuid::fromString(self::IDEA_03_UUID),
            new \DateTime('-10 days 1 minute')
        );
        $ideaHelpPeople->setCategory($category);
        $ideaHelpPeople->addTheme($themeDefense);
        $this->addReference('idea-help-people', $ideaHelpPeople);

        $ideaReduceWaste = new Idea(
            'Réduire le gaspillage',
            'In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.',
            AuthorCategoryEnum::ADHERENT,
            new \DateTime('-26 days 1 minute'),
            new \DateTime('-16 days 1 minute'),
            true,
            $adherent3,
            Uuid::fromString(self::IDEA_04_UUID),
            new \DateTime('-27 days 1 minute')
        );
        $ideaReduceWaste->setCategory($category);
        $ideaReduceWaste->addTheme($themeEcology);
        $this->addReference('idea-reduce-waste', $ideaReduceWaste);

        $ideaReducePupils = new Idea(
            'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
            null,
            AuthorCategoryEnum::ELECTED,
            null,
            null,
            true,
            $adherent13,
            Uuid::fromString(self::IDEA_05_UUID),
            new \DateTime('-5 days 1 minute')
        );
        $this->addReference('idea-reduce-pupils', $ideaReducePupils);

        $ideaReduceNoise = new Idea(
            'Réduire le bruit dans les opens spaces',
            'Curabitur sed leo nec massa lobortis pretium sed ac lacus. In aliquet varius ante.',
            AuthorCategoryEnum::ELECTED,
            new \DateTime('-9 days 15 hours'),
            new \DateTime('+9 hours'),
            true,
            $adherent13,
            Uuid::fromString(self::IDEA_06_UUID),
            new \DateTime('-2 days 1 minute')
        );
        $ideaReduceNoise->setCategory($category);
        $ideaReduceNoise->addTheme($themeEcology);
        $ideaReduceNoise->addNeed($need);
        $this->addReference('idea-noise', $ideaReduceNoise);

        $ideaReduceFoodWaste = new Idea(
            'Réduire le gaspillage alimentaire',
            'Morbi massa lacus, pulvinar ac eros in, imperdiet egestas velit.',
            AuthorCategoryEnum::ELECTED,
            new \DateTime('-23 days 1 minute'),
            new \DateTime('-13 days 1 minute'),
            true,
            $adherent13,
            Uuid::fromString(self::IDEA_07_UUID),
            new \DateTime('-24 days 1 minute')
        );
        $ideaReduceFoodWaste->setCategory($category);
        $ideaReduceFoodWaste->addTheme($themeEcology);
        $this->addReference('idea-food-waste', $ideaReduceFoodWaste);

        $ideaDisabled = new Idea(
            'Idée modérée',
            'Ideas moderatis',
            AuthorCategoryEnum::ELECTED,
            new \DateTime('-26 days 1 minute'),
            new \DateTime('-16 days 1 minute'),
            false,
            $adherent13,
            Uuid::fromString(self::IDEA_08_UUID),
            new \DateTime('-27 days 1 minute')
        );
        $ideaReduceWaste->addTheme($themeEcology);
        $this->addReference('idea-disabled', $ideaDisabled);

        $manager->persist($ideaMakePeace);
        $manager->persist($ideaHelpEcology);
        $manager->persist($ideaHelpPeople);
        $manager->persist($ideaReduceWaste);
        $manager->persist($ideaReducePupils);
        $manager->persist($ideaReduceNoise);
        $manager->persist($ideaReduceFoodWaste);
        $manager->persist($ideaDisabled);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeData::class,
            LoadIdeaNeedData::class,
            LoadIdeaCategoryData::class,
            LoadIdeaThemeData::class,
            LoadIdeaGuidelineData::class,
        ];
    }
}
