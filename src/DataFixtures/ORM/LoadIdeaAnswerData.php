<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Answer;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaAnswerData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adherent = $this->getReference('adherent-1');
        $adherent2 = $this->getReference('adherent-2');
        $ideaPeace = $this->getReference('idea-peace');
        $questionProblem = $this->getReference('question-problem');

        $answerLoremAdherent1 = new Answer(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.',
            $adherent
        );
        $ideaPeace->addAnswer($answerLoremAdherent1);
        $questionProblem->addAnswer($answerLoremAdherent1);
        $this->addReference('answer-lorem-adherent-1', $answerLoremAdherent1);

        $answerLoremAdherent2 = new Answer(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.',
            $adherent2
        );
        $ideaPeace->addAnswer($answerLoremAdherent2);
        $questionProblem->addAnswer($answerLoremAdherent2);
        $this->addReference('answer-lorem-adherent-2', $answerLoremAdherent2);

        $manager->persist($answerLoremAdherent1);
        $manager->persist($answerLoremAdherent2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadIdeaData::class,
            LoadIdeaQuestionData::class,
        ];
    }
}
