<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use App\TerritorialCouncil\Command\AdherentUpdateTerritorialCouncilMembershipsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PhpAmqpLib\Exception\AMQPException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateTerritorialCouncilMembershipsCommand extends Command
{
    protected static $defaultName = 'app:territorial-council:update-memberships';

    private const BATCH_SIZE = 1000;

    /** @var AdherentRepository */
    private $adherentRepository;
    /** @var EntityManagerInterface */
    private $em;
    /** @var MessageBusInterface */
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository
    ) {
        $this->em = $em;
        $this->bus = $bus;
        $this->adherentRepository = $adherentRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, null)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Start updating territorial council memberships');

        $limit = (int) $input->getOption('limit');

        $paginator = $this->getAdherentsPaginator();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        $paginator->getQuery()->setMaxResults($limit && $limit < self::BATCH_SIZE ? $limit : self::BATCH_SIZE);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $adherent) {
                try {
                    $this->bus->dispatch(new AdherentUpdateTerritorialCouncilMembershipsCommand(
                        $adherent->getUuid()
                    ));
                } catch (AMQPException $exception) {
                    $this->bus->dispatch(new AdherentUpdateTerritorialCouncilMembershipsCommand(
                        $adherent->getUuid()
                    ));
                }
                $this->io->progressAdvance();
                ++$offset;

                if ($limit && $limit <= $offset) {
                    break 2;
                }
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->em->clear();
        } while ($offset < $count && (!$limit || $offset < $limit));

        $this->io->progressFinish();
        $this->io->success('Done');
    }

    private function getAdherentsPaginator(): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status')
            ->andWhere('adherent.adherent = true')
            ->setParameter('status', Adherent::ENABLED)
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}
