<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTerritorialCouncilFeedItemData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');
        $referent = $this->getReference('adherent-19');

        $feedItem = new TerritorialCouncilFeedItem(
            $coTerrParis,
            $referent,
            <<<EOD
<p> Lorem <strong>Ipsum</strong></p>
<ol>
    <li>consectetur adipiscing elit</li>
	<li>adipiscing</li>
	<li>ultrices finibus</li>
</ol>
<h2>Le <strong>Phasellus semper</strong> Phasellus semper pulvinar dictum.</h2>
<p>Integer dui nunc, consectetur et tortor sed, varius consectetur est. In nec risus ac orci fringilla suscipit non sed odio. Ut lacinia justo turpis, non suscipit arcu tristique ut. Donec interdum dignissim felis, eu lobortis ligula ultrices vel. Fusce condimentum nulla vel enim ultricies consequat. Pellentesque urna erat, molestie dignissim leo sed, sodales mollis neque. Curabitur et tortor vel metus facilisis consectetur. Sed vitae sollicitudin felis.</p>
EOD
        );
        $manager->persist($feedItem);

        for ($day = 1; $day < 12; ++$day) {
            $feedItem = new TerritorialCouncilFeedItem(
                $coTerrParis,
                $referent,
                "<p>Message du référent $day</p>",
                "-$day days"
            );
            $manager->persist($feedItem);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadTerritorialCouncilData::class,
            LoadTerritorialCouncilMembershipData::class,
        ];
    }
}
