<?php

namespace App\Repository\Election;

use App\Entity\City;
use App\Entity\Election;
use App\Entity\Election\VotePlaceResult;
use App\Entity\ElectionRound;
use App\Entity\VotePlace;
use App\Repository\GeoFilterTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class VotePlaceResultRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotePlaceResult::class);
    }

    public function getReferentExportQuery(Election $election, array $referentTags): Query
    {
        $qb = $this->createElectionQueryBuilder($election, 'vote_result');

        $alias = 'vote_place';

        $this->applyGeoFilter($qb, $referentTags, $alias, "$alias.country", "$alias.postalCode");

        return $qb->getQuery();
    }

    public function getExportQueryByInseeCodes(Election $election, array $inseeCodes): Query
    {
        return $this
            ->createElectionQueryBuilder($election, $alias = 'vote_result')
            ->andWhere('SUBSTRING(vote_place.code, POSITION(\'_\' IN vote_place.code) + 1) IN (:insee_codes)')
            ->setParameter('insee_codes', $inseeCodes)
            ->getQuery()
        ;
    }

    private function createElectionQueryBuilder(Election $election, string $alias = 'vote_result'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->innerJoin($alias.'.votePlace', 'vote_place')
            ->innerJoin($alias.'.electionRound', 'election_round')
            ->innerJoin('election_round.election', 'election')
            ->andWhere('election = :election')
            ->setParameter('election', $election)
        ;
    }

    public function findOneForVotePlace(VotePlace $votePlace, ElectionRound $round): ?VotePlaceResult
    {
        return $this->createQueryBuilder('vr')
            ->where('vr.votePlace = :vote_place')
            ->andWhere('vr.electionRound = :round')
            ->setParameters([
                'vote_place' => $votePlace,
                'round' => $round,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllForCity(City $city, ElectionRound $round): array
    {
        return $this
            ->createQueryBuilder('vote_place_result')
            ->addSelect('vote_place', 'total', 'list')
            ->innerJoin('vote_place_result.votePlace', 'vote_place')
            ->leftJoin('vote_place_result.listTotalResults', 'total')
            ->leftJoin('total.list', 'list')
            ->andWhere('SUBSTRING(vote_place.code, 1, 5) = :insee_code')
            ->andWhere('vote_place_result.electionRound = :election_round')
            ->setParameters([
                'insee_code' => $city->getInseeCode(),
                'election_round' => $round,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
