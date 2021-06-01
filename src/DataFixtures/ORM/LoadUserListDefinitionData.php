<?php

namespace App\DataFixtures\ORM;

use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadUserListDefinitionData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userListDefinitionSupportLaREM = new UserListDefinition(
            UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
            UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM,
            'Sympathisant(e) LaREM'
        );
        $manager->persist($userListDefinitionSupportLaREM);

        $this->addReference(
            'user-list-definition-'.UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM,
            $userListDefinitionSupportLaREM
        );

        $userListDefinitionInstancesMember = new UserListDefinition(
            UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
            UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
            'Participe aux instances'
        );
        $manager->persist($userListDefinitionInstancesMember);

        $this->addReference(
            'user-list-definition-'.UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
            $userListDefinitionInstancesMember
        );

        $manager->persist(new UserListDefinition(UserListDefinitionEnum::TYPE_LRE, 'lre-label-1', 'LRE label 1'));
        $manager->persist(new UserListDefinition(UserListDefinitionEnum::TYPE_LRE, 'lre-label-2', 'LRE label 2'));

        $manager->flush();
    }
}
