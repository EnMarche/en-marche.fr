<?php

namespace App\Command\VotingPlatform;

use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\CommitteeRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitializeElectionsCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:step-1:initialize-elections';

    /** @var DesignationRepository */
    private $designationRepository;
    /** @var SymfonyStyle */
    private $io;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var CommitteeRepository */
    private $committeeRepository;
    /** @var TerritorialCouncilRepository */
    private $territorialCouncilRepository;

    protected function configure()
    {
        $this->setDescription('Voting Platform: step 1: initialize elections for committees, territorial councils, etc...');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $designations = $this->designationRepository->getIncomingCandidacyDesignations($date);

        $this->io->progressStart();

        foreach ($designations as $designation) {
            if ($designation->isCommitteeType()) {
                $this->configureCommitteeElections($designation);
            } elseif ($designation->isCopolType()) {
                $this->configureTerritorialCouncilElections($designation);
            } else {
                $this->io->error(sprintf('Unhandled designation type "%s"', $designation->getType()));
            }
        }

        $this->io->progressFinish();

        return 0;
    }

    private function configureCommitteeElections(Designation $designation): void
    {
        while ($committees = $this->committeeRepository->findAllWithoutStartedElection($designation)) {
            foreach ($committees as $committee) {
                $committee->setCurrentElection(new CommitteeElection($designation));

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear(Committee::class);
            $this->entityManager->clear(CommitteeElection::class);
        }
    }

    private function configureTerritorialCouncilElections(Designation $designation): void
    {
        while ($councils = $this->territorialCouncilRepository->findAllWithoutStartedElection($designation)) {
            foreach ($councils as $council) {
                $council->setCurrentElection(new Election($designation));

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear(TerritorialCouncil::class);
            $this->entityManager->clear(Election::class);
        }
    }

    /** @required */
    public function setDesignationRepository(DesignationRepository $designationRepository): void
    {
        $this->designationRepository = $designationRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setCommitteeRepository(CommitteeRepository $committeeRepository): void
    {
        $this->committeeRepository = $committeeRepository;
    }

    /** @required */
    public function setTerritorialCouncilRepository(TerritorialCouncilRepository $territorialCouncilRepository): void
    {
        $this->territorialCouncilRepository = $territorialCouncilRepository;
    }
}
