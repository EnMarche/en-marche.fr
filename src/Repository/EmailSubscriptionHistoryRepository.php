<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\Reporting\EmailSubscriptionHistoryAction;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmailSubscriptionHistoryRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailSubscriptionHistory::class);
    }

    /**
     * Count active adherents (not users) history for each specified subscription types in the referent managed area before the specified date.
     */
    public function countAllByTypeForReferentManagedArea(
        Adherent $referent,
        array $subscriptionTypeCodes,
        \DateTimeInterface $until
    ): array {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('history')
            ->select('history.adherentUuid, subscriptionType.code, history.action, COUNT(history) AS count')
            ->join('history.referentTags', 'tags')
            ->join('history.subscriptionType', 'subscriptionType')
            ->where('tags IN (:tags)')
            ->andWhere('subscriptionType.code IN (:subscriptions)')
            ->andWhere('history.date <= :until')
            ->groupBy('history.adherentUuid, subscriptionType.code, history.action')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('subscriptions', $subscriptionTypeCodes)
            ->setParameter('until', $until)
            ->getQuery()
        ;

        // Let's cache past data as they are never going to change
        $beginningOfThisMonth = (new Chronos('first day of this month'))->setTime(0, 0);
        if ($beginningOfThisMonth > $until) {
            $qb->useResultCache(true, 5184000); // 60 days
        }

        $results = $qb->getArrayResult();
        $countBySubscriptionType = [];

        foreach ($results as ['adherentUuid' => $adherentUuid, 'action' => $action, 'code' => $subscriptionTypeCode, 'count' => $count]) {
            $adherentUuid = (string) $adherentUuid;

            $sign = EmailSubscriptionHistoryAction::SUBSCRIBE === $action ? 1 : -1;

            if (!isset($countBySubscriptionType[$subscriptionTypeCode][$adherentUuid])) {
                $countBySubscriptionType[$subscriptionTypeCode][$adherentUuid] = 0;
            }
            $countBySubscriptionType[$subscriptionTypeCode][$adherentUuid] += $sign * $count;
        }

        // If one adherent has multiple referent tags (With paris district for example), his email subscription must count for one and not many.
        // That's why we remove values equal to 0 and then count the number of entries we have by type.
        foreach ($subscriptionTypeCodes as $type) {
            if (isset($countBySubscriptionType[$type]) && \is_array($countBySubscriptionType[$type])) {
                $countBySubscriptionType[$type] = array_filter($countBySubscriptionType[$type]);
                $countBySubscriptionType[$type] = \count($countBySubscriptionType[$type]);
            } else {
                $countBySubscriptionType[$type] = 0;
            }
        }

        return $countBySubscriptionType;
    }
}
