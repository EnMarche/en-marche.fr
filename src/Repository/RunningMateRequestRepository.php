<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RunningMateRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RunningMateRequest::class);
    }

    private function createListQueryBuilder(string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->addSelect("CASE WHEN $alias.id IS NOT NULL THEN 1 ELSE 0 END AS isAdherent")
            ->leftJoin(Adherent::class, 'a', Join::WITH, "a.emailAddress = $alias.emailAddress AND a.adherent = 1")
            ->addOrderBy("$alias.lastName", 'ASC')
            ->addOrderBy("$alias.firstName", 'ASC')
        ;
    }

    private function handleListQueryResults(QueryBuilder $queryBuilder): array
    {
        $data = [];
        foreach ($queryBuilder->getQuery()->getResult() as $result) {
            /** @var RunningMateRequest $runningMate */
            $runningMate = $result[0];
            $runningMate->setIsAdherent($result['isAdherent']);
            $data[] = $runningMate;
        }

        return $data;
    }

    public function findForReferent(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        $qb = $this->createListQueryBuilder('r')
            ->innerJoin('r.referentTags', 'tag')
            ->andWhere('tag IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
        ;

        return $this->handleListQueryResults($qb);
    }

    public function findForMunicipalChief(Adherent $municipalChief): array
    {
        if (!$municipalChief->isMunicipalChief()) {
            return [];
        }

        $qb = $this->createListQueryBuilder('r');
        foreach ($municipalChief->getMunicipalChiefManagedArea()->getCodes() as $key => $code) {
            $qb
                ->orWhere("FIND_IN_SET(:codes_$key, r.favoriteCities) > 0")
                ->setParameter("codes_$key", $code)
            ;
        }

        return $this->handleListQueryResults($qb);
    }
}
