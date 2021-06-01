<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadPoliticalCommitteeFeedItemData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var PoliticalCommittee $coPolParis */
        $coPolParis = $this->getReference('coPol_75');
        $referent = $this->getReference('adherent-19');

        $feedItem = new PoliticalCommitteeFeedItem(
            $coPolParis,
            $referent,
            <<<EOD
<p> Lorem <strong>Ipsum</strong></p>
<ol>
    <li>dolor augue</li>
	<li>aliquam</li>
	<li>rutrum arcu nulla</li>
</ol>
<h2><strong>Vivamus nulla ligula,</strong> Suspendisse condimentum vulputate magna.</h2>
<p>Nulla viverra felis quis ullamcorper porttitor.</p>
EOD
        );
        $manager->persist($feedItem);

        for ($day = 1; $day < 12; ++$day) {
            $feedItem = new PoliticalCommitteeFeedItem(
                $coPolParis,
                $referent,
                "<p>Message du référent $day dans le CoPol</p>",
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
