<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\Reporting\EmailSubscriptionHistoryAction;
use App\Repository\EmailSubscriptionHistoryRepository;
use App\Subscription\SubscriptionTypeEnum;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;

class EmailSubscriptionHistoryHandler
{
    private $em;
    private $historyRepository;

    public function __construct(EntityManagerInterface $em, EmailSubscriptionHistoryRepository $historyRepository)
    {
        $this->em = $em;
        $this->historyRepository = $historyRepository;
    }

    /**
     * Useful on email subscription settings update
     *
     * - $adherent is up-to-date with new email subscriptions
     * - $oldEmailsSubscriptions is basically the email subscriptions that we have in DB
     *
     * => New and old subscriptions are compared and only the relevant subscription histories are created.
     */
    public function handleSubscriptionsUpdate(Adherent $adherent, array $oldEmailsSubscriptions): void
    {
        $subscriptions = array_diff($adherent->getSubscriptionTypes(), $oldEmailsSubscriptions);
        $unsubscriptions = array_diff($oldEmailsSubscriptions, $adherent->getSubscriptionTypes());
        $referentTags = $adherent->getReferentTags()->toArray();

        $this->createEmailSubscriptionHistory($adherent, $subscriptions, $referentTags, EmailSubscriptionHistoryAction::SUBSCRIBE());
        $this->createEmailSubscriptionHistory($adherent, $unsubscriptions, $referentTags, EmailSubscriptionHistoryAction::UNSUBSCRIBE());

        $this->em->flush();
    }

    /**
     * Useful on address update
     *
     * - $adherent is up-to-date with new referentTags
     * - $oldReferentTags is basically the referent tags that we have in DB
     *
     * => New and old referent tags are compared and only the relevant subscription histories are created.
     */
    public function handleReferentTagsUpdate(Adherent $adherent, array $oldReferentTags): void
    {
        $subscribedReferentTags = array_diff($adherent->getReferentTags()->toArray(), $oldReferentTags);
        $unsubscribedReferentTags = array_diff($oldReferentTags, $adherent->getReferentTags()->toArray());
        $subscriptions = $adherent->getSubscriptionTypes();

        $this->createEmailSubscriptionHistory($adherent, $subscriptions, $subscribedReferentTags, EmailSubscriptionHistoryAction::SUBSCRIBE());
        $this->createEmailSubscriptionHistory($adherent, $subscriptions, $unsubscribedReferentTags, EmailSubscriptionHistoryAction::UNSUBSCRIBE());
    }

    public function handleSubscriptions(Adherent $adherent): void
    {
        $this->createEmailSubscriptionHistory(
            $adherent,
            $adherent->getSubscriptionTypes(),
            $adherent->getReferentTags()->toArray(),
            EmailSubscriptionHistoryAction::SUBSCRIBE()
        );
    }

    public function handleUnsubscriptions(Adherent $adherent): void
    {
        $this->createEmailSubscriptionHistory(
            $adherent,
            $adherent->getSubscriptionTypes(),
            $adherent->getReferentTags()->toArray(),
            EmailSubscriptionHistoryAction::UNSUBSCRIBE()
        );
    }

    private function createEmailSubscriptionHistory(
        Adherent $adherent,
        array $subscriptions,
        array $referentTags,
        EmailSubscriptionHistoryAction $action
    ): void {
        foreach ($subscriptions as $subscription) {
            $this->em->persist(
                new EmailSubscriptionHistory($adherent, $subscription, $referentTags, $action)
            );
        }
        $this->em->flush();
    }

    public function queryCountByMonth(Adherent $referent, int $months = 6): array
    {
        $countByMonth = [];

        foreach (range(0, $months - 1) as $monthInterval) {
            $until = $monthInterval
                ? (new Chronos("last day of -$monthInterval month"))->setTime(23, 59, 59, 999)
                : new Chronos()
            ;

            $subscriptions = $this->historyRepository->countAllByTypeForReferentManagedArea(
                $referent,
                [
                    SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                    SubscriptionTypeEnum::REFERENT_EMAIL,
                ],
                $until
            );

            $subscriptions['date'] = $until->format('Y-m');
            $countByMonth[] = $subscriptions;
        }

        return $countByMonth;
    }
}
