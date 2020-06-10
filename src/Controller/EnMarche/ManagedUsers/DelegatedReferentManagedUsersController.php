<?php

namespace App\Controller\EnMarche\ManagedUsers;

use App\Controller\CanaryControllerTrait;
use App\Entity\Committee;
use App\Entity\MyTeam\DelegatedAccess;
use App\ManagedUsers\ManagedUsersFilter;
use App\Subscription\SubscriptionTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent-delegue", name="app_referent_managed_users_delegated_", methods={"GET"})
 *
 * @Security("is_granted('IS_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_ADHERENTS', 'referent')")
 */
class DelegatedReferentManagedUsersController extends ReferentManagedUsersController
{
    use CanaryControllerTrait;

    protected function createFilterModel(): ManagedUsersFilter
    {
        $this->disableInProduction();

        /** @var DelegatedAccess $delegatedAccess */
        $delegatedAccess = $this->get('request_stack')->getMasterRequest()->attributes->get('delegated_access');
        if (!$delegatedAccess) {
            throw new \LogicException('No delegated access found');
        }

        return new ManagedUsersFilter(
            SubscriptionTypeEnum::REFERENT_EMAIL,
            $delegatedAccess->getDelegator()->getReferentTags()->toArray(),
            $delegatedAccess->getRestrictedCommittees()->map(static function (Committee $committee) {
                return $committee->getUuidAsString();
            })->toArray(),
            $delegatedAccess->getRestrictedCities(),
        );
    }
}
