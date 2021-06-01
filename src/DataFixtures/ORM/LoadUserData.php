<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\PostAddress;
use App\Membership\AdherentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadUserData extends Fixture
{
    public const USER_1_UUID = '313bd28f-efc8-57c9-8ab7-2106c8be9699';
    public const USER_2_UUID = '413bd28f-57c9-efc8-8ab7-2106c8be9690';
    public const USER_3_UUID = '513bd28f-8ab7-57c9-efc8-2106c8be9690';
    public const USER_4_UUID = '94173303-dea2-4d97-bc15-86e785d85a0d';

    public const USER_3_TOKEN = 'c997dd323ef4b53b3d31881fa495bddb3d0c3b55';
    public const USER_4_TOKEN = 'b17bcc506c47008faa2e5aa59f20ac1e70ea0911';

    private $adherentFactory;

    public function __construct(AdherentFactory $adherentFactory)
    {
        $this->adherentFactory = $adherentFactory;
    }

    public function load(ObjectManager $manager)
    {
        // Create adherent users list
        $user1 = $this->adherentFactory->createFromArray([
            'uuid' => self::USER_1_UUID,
            'password' => LoadAdherentData::DEFAULT_PASSWORD,
            'email' => 'simple-user@example.ch',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', null, ''),
            'isAdherent' => false,
        ]);
        $key1 = AdherentActivationToken::generate($user1);
        $user1->activate($key1, '2017-01-25 19:34:02');

        $user2 = $this->adherentFactory->createFromArray([
            'uuid' => self::USER_2_UUID,
            'password' => LoadAdherentData::DEFAULT_PASSWORD,
            'email' => 'simple-user-not-activated@example.ch',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', null, ''),
            'isAdherent' => false,
        ]);
        $user3 = $this->adherentFactory->createFromArray([
            'uuid' => self::USER_3_UUID,
            'password' => LoadAdherentData::DEFAULT_PASSWORD,
            'email' => 'simple-user-disabled@example.ch',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', null, ''),
            'isAdherent' => false,
        ]);
        $key3 = AdherentActivationToken::generate($user3);
        $user3->activate($key3, '2017-01-25 19:34:02');
        $user3->setStatus(Adherent::DISABLED);
        $resetPasswordToken1 = AdherentResetPasswordToken::create(self::USER_3_UUID, self::USER_3_TOKEN);
        $manager->persist($resetPasswordToken1);

        // Create adherent users list
        $user4 = $this->adherentFactory->createFromArray([
            'uuid' => self::USER_4_UUID,
            'password' => LoadAdherentData::DEFAULT_PASSWORD,
            'email' => 'simple-test-user@example.ch',
            'first_name' => 'Simple',
            'last_name' => 'User',
            'address' => PostAddress::createForeignAddress('CH', '8057', null, ''),
            'isAdherent' => false,
        ]);
        $key4 = AdherentActivationToken::generate($user4);
        $user4->activate($key4, '2017-01-25 19:34:02');
        $resetPasswordToken2 = AdherentResetPasswordToken::create(self::USER_4_UUID, self::USER_4_TOKEN);
        $manager->persist($resetPasswordToken2);

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user4);
        $manager->flush();
    }
}
