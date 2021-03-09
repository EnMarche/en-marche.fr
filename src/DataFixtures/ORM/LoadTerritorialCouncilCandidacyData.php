<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Image\ImageManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadTerritorialCouncilCandidacyData extends Fixture implements DependentFixtureInterface
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        // CO TERR 75
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');

        $this->createCandidacy(
            $manager,
            $coTerrParis,
            $this->getReference('adherent-5'),
            $this->getReference('adherent-12'),
            TerritorialCouncilQualityEnum::CITY_COUNCILOR,
            __DIR__.'/../../../app/data/dist/avatar_homme_02.jpg'
        );

        $candidacy = $this->createCandidacy(
            $manager,
            $coTerrParis,
            $this->getReference('adherent-3'),
            $this->getReference('adherent-19'),
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            __DIR__.'/../../../app/data/dist/avatar_homme_01.jpg'
        );

        foreach ($candidacy->getInvitations() as $invitation) {
            $invitation->accept();
        }

        $invitedCandidate = $this->createCandidacy(
            $manager,
            $coTerrParis,
            $this->getReference('adherent-19'),
            null,
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            __DIR__.'/../../../app/data/dist/avatar_femme_01.jpg'
        );

        $candidacy->setBinome($invitedCandidate);
        $invitedCandidate->setBinome($candidacy);
        $invitedCandidate->updateFromBinome();

        $candidacy->confirm();
        $invitedCandidate->confirm();

        // CO TERR 92
        /** @var TerritorialCouncil $coTerr */
        $coTerr = $this->getReference('coTerr_92');

        $candidacy = $this->createCandidacy(
            $manager,
            $coTerr,
            $this->getReference('municipal-manager-1'),
            $this->getReference('senatorial-candidate'),
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            __DIR__.'/../../../app/data/dist/avatar_femme_01.jpg'
        );

        foreach ($candidacy->getInvitations() as $invitation) {
            $invitation->accept();
        }

        $invitedCandidate = $this->createCandidacy(
            $manager,
            $coTerr,
            $this->getReference('senatorial-candidate'),
            null,
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            __DIR__.'/../../../app/data/dist/avatar_homme_01.jpg'
        );

        $candidacy->setBinome($invitedCandidate);
        $invitedCandidate->setBinome($candidacy);
        $invitedCandidate->updateFromBinome();

        $candidacy->confirm();
        $invitedCandidate->confirm();

        $candidacy = $this->createCandidacy(
            $manager,
            $coTerr,
            $this->getReference('adherent-2'),
            null,
            TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR,
            __DIR__.'/../../../app/data/dist/avatar_homme_01.jpg'
        );
        $candidacy->confirm();

        $manager->flush();
    }

    private function createCandidacy(
        ObjectManager $manager,
        TerritorialCouncil $coTerr,
        Adherent $adherent,
        ?Adherent $invited,
        string $quality,
        string $imagePath
    ): Candidacy {
        $candidacy = new Candidacy($adherent->getTerritorialCouncilMembership(), $coTerr->getCurrentElection(), $adherent->getGender());

        $candidacy->setQuality($quality);
        $candidacy->setBiography('Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.');
        $candidacy->setFaithStatement('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.');
        $candidacy->setIsPublicFaithStatement(true);

        $candidacy->setImage(new UploadedFile(
             $imagePath,
             'image.jpg',
             'image/jpeg',
             null,
             null,
             true
         ));

        if ($invited) {
            $candidacy->addInvitation($invitation = new CandidacyInvitation());
            $invitation->setMembership($invited->getTerritorialCouncilMembership());
        }

        $manager->persist($candidacy);
        $this->imageManager->saveImage($candidacy);

        return $candidacy;
    }

    public function getDependencies()
    {
        return [
            LoadTerritorialCouncilMembershipData::class,
        ];
    }
}
