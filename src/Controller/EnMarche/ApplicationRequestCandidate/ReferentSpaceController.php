<?php

namespace AppBundle\Controller\EnMarche\ApplicationRequestCandidate;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/", name="app_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentSpaceController extends AbstractApplicationRequestController
{
    private const SPACE_NAME = 'referent';

    protected function getApplicationRequests(ApplicationRequestRepository $repository, string $type): array
    {
        return $repository->findAllForReferentTags($this->getUser()->getManagedArea()->getTags()->toArray(), $type);
    }

    protected function getSpaceName(): string
    {
        return self::SPACE_NAME;
    }

    protected function checkAccess(ApplicationRequest $request = null): void
    {
        // Block access to the individual application request
        if ($request) {
            throw $this->createNotFoundException();
        }

        if (
            array_filter(
                $this->getUser()->getManagedAreaTagCodes(),
                function ($code) { return 0 === strpos($code, '75'); }
            )
        ) {
            throw $this->createNotFoundException();
        }
    }
}
