<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeCandidacyInvitation;
use App\Image\ImageManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadCommitteeCandidacyData extends Fixture implements DependentFixtureInterface
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        $adherentCandidates = [
            [
                'adherent' => 'assessor-1',
                'committee' => 'committee-6',
                'with_photo' => true,
                'enable_vote' => true,
            ],
            [
                'adherent' => 'adherent-2',
                'committee' => 'committee-6',
                'enable_vote' => true,
            ],
            [
                'adherent' => 'adherent-3',
                'committee' => 'committee-4',
            ],
            [
                'adherent' => 'adherent-49',
                'committee' => 'committee-12',
                'binome' => 'municipal-manager-1',
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-21',
                'committee' => 'committee-13',
                'binome' => 'adherent-22',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-23',
                'committee' => 'committee-13',
                'binome' => 'adherent-24',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-25',
                'committee' => 'committee-13',
                'binome' => 'adherent-26',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-27',
                'committee' => 'committee-13',
                'binome' => 'adherent-28',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-29',
                'committee' => 'committee-14',
                'binome' => 'adherent-30',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-32',
                'committee' => 'committee-15',
                'binome' => 'adherent-33',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-34',
                'committee' => 'committee-15',
                'binome' => 'adherent-35',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
            [
                'adherent' => 'adherent-36',
                'committee' => 'committee-15',
                'binome' => 'adherent-37',
                'confirmed' => true,
                'public_faith_statement' => true,
                'with_photo' => true,
            ],
        ];

        foreach ($adherentCandidates as $i => $row) {
            /** @var Adherent $adherent */
            $adherent = $this->getReference($row['adherent']);
            /** @var Committee $committee */
            $committee = $this->getReference($row['committee']);
            $membership = $adherent->getMembershipFor($committee);
            $election = $committee->getCurrentElection();

            if (!empty($row['enable_vote'])) {
                $membership->enableVote();
            }

            $candidacy = $this->createCandidacy($committee, $adherent, !empty($row['with_photo']));

            if (!empty($row['binome'])) {
                $candidacy->setFaithStatement('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.');
                $candidacy->setIsPublicFaithStatement(!empty($row['public_faith_statement']));

                /** @var Adherent $invited */
                $invited = $this->getReference($row['binome']);
                $candidacy->addInvitation($invitation = new CommitteeCandidacyInvitation());
                $invitation->setMembership($invited->getMembershipFor($committee));

                if (!empty($row['confirmed'])) {
                    $candidacyBinome = $this->createCandidacy($committee, $invited, !empty($row['with_photo']));
                    $manager->persist($candidacyBinome);
                    $candidacy->candidateWith($candidacyBinome);
                    $candidacy->syncWithOtherCandidacies();

                    $candidacy->confirm();
                    $candidacyBinome->confirm();
                    $invitation->accept();

                    $manager->persist($candidacyBinome);
                }
            }

            $manager->persist($candidacy);
            $this->setReference(sprintf('committee-candidacy-%s-%d', $election->getDesignationType(), ($i + 1)), $candidacy);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
        ];
    }

    private function createCandidacy(
        Committee $committee,
        Adherent $adherent,
        bool $withPhoto = false
    ): CommitteeCandidacy {
        $adherent->getMembershipFor($committee)->addCommitteeCandidacy(
            $candidacy = new CommitteeCandidacy($committee->getCommitteeElection(), $adherent->getGender())
        );
        $candidacy->setBiography('Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.');

        if ($withPhoto) {
            $candidacy->setImage(new UploadedFile(
                sprintf('%s/../../../app/data/dist/avatar_%s_0%d.jpg', __DIR__, $adherent->isFemale() ? 'femme' : 'homme', rand(1, 2)),
                'image.jpg',
                'image/jpeg',
                null,
                null,
                true
            ));

            $this->imageManager->saveImage($candidacy);
        }

        return $candidacy;
    }
}
