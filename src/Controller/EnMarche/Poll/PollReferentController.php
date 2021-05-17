<?php

namespace App\Controller\EnMarche\Poll;

use App\Entity\Adherent;
use App\Poll\PollSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/question-du-jour", name="app_referent_polls_")
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_POLLS'))")
 */
class PollReferentController extends AbstractPollController
{
    protected function getSpaceName(): string
    {
        return PollSpaceEnum::REFERENT_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());
    }
}
