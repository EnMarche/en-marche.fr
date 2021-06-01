<?php

namespace App\DataFixtures\ORM;

use App\Address\Address;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\Transaction;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDonationData extends Fixture implements DependentFixtureInterface
{
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        $donator0 = $this->createDonator('000050', $this->getReference('adherent-1'));
        $donator1 = $this->createDonator('000051', $this->getReference('adherent-4'));
        $donator2 = $this->createDonator('000052', $this->getReference('adherent-3'));

        $donationNormal = $this->createDonation(
            $donator0,
            50.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2020/01/01 10:30:00',
            '123456'
        );
        $this->createTransaction($donationNormal);

        $donationMonthly = $this->createDonation(
            $donator0,
            42.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2019/12/01 11:00:00'
        );
        $this->createTransaction($donationMonthly, Transaction::PAYBOX_CARD_UNAUTHORIZED);
        $this->createTransaction($donationMonthly, Transaction::PAYBOX_SUCCESS, '+1 month');

        $this->createDonation(
            $donator0,
            30.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CHECK,
            '2019/01/12 12:00:00'
        );

        $donation0 = $this->createDonation(
            $donator1,
            50.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2020/01/02 13:37:00',
            '654321'
        );
        $this->createTransaction($donation0);

        $donation1 = $this->createDonation(
            $donator2,
            50.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2019/12/04 12:00:00'
        );
        $this->createTransaction($donation1);

        $donation2 = $this->createDonation(
            $donator2,
            40.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2020/01/04 12:30:00',
            '654321'
        );
        $this->createTransaction($donation2);

        $donation3 = $this->createDonation(
            $donator2,
            60.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2019/12/05 15:00:00'
        );
        $this->createTransaction($donation3);
        $this->createTransaction($donation3, Transaction::PAYBOX_SUCCESS, '+1 month');
        $donation3->stopSubscription();

        $donation4 = $this->createDonation(
            $donator2,
            100.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2020/01/06 19:00:00'
        );
        $this->createTransaction($donation4);

        $donator0->computeLastSuccessfulDonation();
        $donator1->computeLastSuccessfulDonation();
        $donator2->computeLastSuccessfulDonation();

        $manager->persist($donator0);
        $manager->persist($donator1);
        $manager->persist($donator2);

        $manager->flush();
    }

    public function createDonator(string $accountId, Adherent $adherent): Donator
    {
        $donator = new Donator(
            $adherent->getLastName(),
            $adherent->getFirstName(),
            $adherent->getCityName(),
            $adherent->getCountry(),
            $adherent->getEmailAddress(),
            $adherent->getGender()
        );

        $donator->setIdentifier($accountId);
        $donator->setAdherent($adherent);

        return $donator;
    }

    public function createDonation(
        Donator $donator,
        float $amount = 50.0,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB,
        string $donatedAt = null,
        string $code = null
    ): Donation {
        $donation = new Donation(
            $uuid = Uuid::uuid4(),
            $type,
            $amount * 100,
            $donatedAt ? \DateTimeImmutable::createFromFormat('Y/m/d H:i:s', $donatedAt) : new \DateTimeImmutable(),
            $donator->getAdherent()->getPostAddress(),
            '127.0.0.1',
            $duration,
            $uuid->toString().'_'.$this->slugify->slugify($donator->getFullName()),
            Address::FRANCE,
            $code,
            $donator
        );

        $donator->addDonation($donation);

        return $donation;
    }

    public function createTransaction(
        Donation $donation,
        string $resultCode = Transaction::PAYBOX_SUCCESS,
        string $dateModifier = null
    ): Transaction {
        /** @var \DateTimeImmutable $donatedAt */
        $donatedAt = $donation->getDonatedAt();

        if ($dateModifier) {
            $donatedAt = $donatedAt->modify($dateModifier);
        }

        return $donation->processPayload([
            'result' => $resultCode,
            'authorization' => 'test',
            'subscription' => $donation->getDuration() ? Uuid::uuid1()->toString() : null,
            'transaction' => Uuid::uuid4()->toString(),
            'date' => $donatedAt->format('dmY'),
            'time' => $donatedAt->format('H:i:s'),
        ]);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadDonatorIdentifierData::class,
        ];
    }
}
