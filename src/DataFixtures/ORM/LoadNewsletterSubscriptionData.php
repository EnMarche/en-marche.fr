<?php

namespace App\DataFixtures\ORM;

use App\Newsletter\NewsletterSubscriptionFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadNewsletterSubscriptionData extends Fixture implements OrderedFixtureInterface
{
    private $newsletterSubscriptionFactory;

    public function __construct(NewsletterSubscriptionFactory $newsletterSubscriptionFactory)
    {
        $this->newsletterSubscriptionFactory = $newsletterSubscriptionFactory;
    }

    public function load(ObjectManager $manager)
    {
        $newsletterSubscription92 = $this->newsletterSubscriptionFactory->create('abc@en-marche-dev.fr', '92110');
        $newsletterSubscription92->setConfirmedAt(new \DateTime('2020-08-03'));
        $this->addReference('news-sub-92', $newsletterSubscription92);

        $newsletterSubscription77 = $this->newsletterSubscriptionFactory->create('def@en-marche-dev.fr', '77000');
        $this->addReference('news-sub-77', $newsletterSubscription77);

        $manager->persist($this->newsletterSubscriptionFactory->create('foobar@en-marche-dev.fr', '35420'));
        $manager->persist($this->newsletterSubscriptionFactory->create('referent@en-marche-dev.fr', '35420'));
        $manager->persist($newsletterSubscription92);
        $manager->persist($newsletterSubscription77);
        $manager->persist($this->newsletterSubscriptionFactory->create('ghi@en-marche-dev.fr', '77123'));

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
