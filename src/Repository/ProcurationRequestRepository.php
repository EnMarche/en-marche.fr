<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\ProcurationRequest;
use App\Procuration\Filter\ProcurationRequestFilters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProcurationRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcurationRequest::class);
    }

    /**
     * @return ProcurationRequest[]
     */
    public function findByEmailAddress(string $emailAddress): array
    {
        return $this
            ->createQueryBuilder('pr')
            ->where('LOWER(pr.emailAddress) = :emailAddress')
            ->setParameter('emailAddress', mb_strtolower(trim($emailAddress)))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ProcurationRequest[]
     */
    public function findAllForExport(): array
    {
        return $this->createQueryBuilder('pr')
            ->select(
                'pr.id AS request_id',
                'pr.gender AS request_birthdate',
                'pr.firstNames AS request_firstNames',
                'pr.lastName AS request_lastName',
                'pr.emailAddress AS request_emailAddress',
                'pr.address AS request_address',
                'pr.postalCode AS request_postalCode',
                'pr.city AS request_city',
                'pr.cityName AS request_cityName',
                'pr.country AS request_country',
                'pr.voteOffice AS request_voteOffice',
                'pr.votePostalCode AS request_votePostalCode',
                'pr.voteCity AS request_voteCity',
                'pr.voteCityName AS request_voteCityName',
                'pr.voteCountry AS request_voteCountry',
                'GROUP_CONCAT(er.label SEPARATOR \'\\n\') AS request_electionRounds',
                'pr.reason AS request_reason',
                'pr.processedAt AS request_processedAt',

                'pp.id AS proposal_id',
                'pp.gender AS proposal_birthdate',
                'pp.firstNames AS proposal_firstNames',
                'pp.lastName AS proposal_lastName',
                'pp.emailAddress AS proposal_emailAddress',
                'pp.address AS proposal_address',
                'pp.postalCode AS proposal_postalCode',
                'pp.city AS proposal_city',
                'pp.cityName AS proposal_cityName',
                'pp.country AS proposal_country',
                'pp.voteOffice AS proposal_voteOffice',
                'pp.votePostalCode AS proposal_votePostalCode',
                'pp.voteCity AS proposal_voteCity',
                'pp.voteCityName AS proposal_voteCityName',
                'pp.voteCountry AS proposal_voteCountry',
                'pp.electionPresidentialFirstRound AS proposal_electionPresidentialFirstRound',
                'pp.electionPresidentialSecondRound AS proposal_electionPresidentialSecondRound',
                'pp.electionLegislativeFirstRound AS proposal_electionLegislativeFirstRound',
                'pp.electionLegislativeSecondRound AS proposal_electionLegislativeSecondRound'
            )
            ->join('pr.foundProxy', 'pp')
            ->leftJoin('pr.electionRounds', 'er')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findMatchingRequests(Adherent $manager, ProcurationRequestFilters $filters): array
    {
        if (!$manager->isProcurationManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder($alias = 'pr');

        $filters->apply($qb, $alias);

        $requests = $this->addAndWhereManagedBy($qb, $manager)
            ->addGroupBy("$alias.id")
            ->getQuery()
            ->getResult()
        ;

        if ($filters->matchUnprocessedRequests()) {
            return $this->findRequests($requests);
        }

        foreach ($requests as $k => $request) {
            $requests[$k] = [
                'data' => $request,
                'matchingProxiesCount' => 1,
            ];
        }

        return $requests;
    }

    public function countMatchingRequests(Adherent $manager, ProcurationRequestFilters $filters): int
    {
        if (!$manager->isProcurationManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder('pr');

        $filters->apply($qb, 'pr');

        return (int) $this->addAndWhereManagedBy($qb, $manager)
            ->select('COUNT(DISTINCT pr.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param ProcurationRequest[] $requests
     */
    private function findRequests(array $requests): array
    {
        if (!\count($requests)) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('COUNT(DISTINCT pp.id)')
            ->from('App:ProcurationProxy', 'pp')
            ->andWhere('pp.disabled = false')
            ->andWhere('pp.reliability >= 0')
        ;

        /**
         * @var ProcurationRequest
         */
        foreach ($requests as $key => $request) {
            $proxiesCountQuery = $this->andWhereRoundsMatch(clone $qb, $request->getElectionRounds()->toArray());
            $proxiesCountQuery = ProcurationProxyRepository::addAndWhereCountryConditions($proxiesCountQuery, $request->isRequestFromFrance());

            $proxiesCountQuery->setParameter('votePostalCodePrefix', substr($request->getVotePostalCode(), 0, 2));
            $proxiesCountQuery->setParameter('voteCityName', $request->getVoteCityName());
            $proxiesCountQuery->setParameter('voteCountry', $request->getVoteCountry());

            $requests[$key] = [
                'data' => $request,
                'matchingProxiesCount' => (int) $proxiesCountQuery->getQuery()->getSingleScalarResult(),
            ];
        }

        return $requests;
    }

    public function isManagedBy(Adherent $procurationManager, ProcurationRequest $procurationRequest): bool
    {
        if (!$procurationManager->isProcurationManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('pr')
            ->select('COUNT(pr)')
            ->where('pr.id = :id')
            ->setParameter('id', $procurationRequest->getId())
        ;

        return (bool) $this->addAndWhereManagedBy($qb, $procurationManager)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findRemindersBatchToSend($offset = 0, $limit = 25): array
    {
        return $this->createQueryBuilder('pr')
            ->select('pr', 'pp', 'a')
            ->join('pr.foundProxy', 'pp')
            ->leftJoin('pr.foundBy', 'a')
            ->leftJoin('pr.electionRounds', 'er')
            ->where('er.date < :next_round')
            ->setParameter('next_round', new \DateTime('+3 days'))
            ->andWhere('er.date > :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('pr.processed = true')
            ->andWhere('pr.reminded = 0')
            ->andWhere('pr.processedAt <= :matchDate')
            ->setParameter('matchDate', new \DateTime('-48 hours'))
            ->orderBy('pr.processedAt', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countRemindersToSend(): int
    {
        return (int) $this->createQueryBuilder('pr')
            ->select('COUNT(pr)')
            ->join('pr.foundProxy', 'pp')
            ->where('pr.processed = true')
            ->andWhere('pr.reminded = 0')
            ->andWhere('pr.processedAt <= :matchDate')
            ->setParameter('matchDate', new \DateTime('-48 hours'))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager): QueryBuilder
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($procurationManager->getProcurationManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'pr.voteCountry = \'FR\'',
                        $qb->expr()->like('pr.votePostalCode', ':code'.$key)
                    )
                );
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('pr.voteCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        return $qb->andWhere($codesFilter);
    }

    private function andWhereRoundsMatch(QueryBuilder $qb, array $electionRounds): QueryBuilder
    {
        if (!$electionRounds) {
            return $qb;
        }

        $matches = [];

        foreach ($electionRounds as $i => $round) {
            $matches[] = ":round_$i MEMBER OF pp.electionRounds";
            $qb->setParameter("round_$i", $round);
        }

        return $qb->andWhere($qb->expr()->andX(...$matches));
    }
}
