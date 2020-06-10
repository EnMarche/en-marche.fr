<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DelegatedAccessesVoter extends Voter
{
    private const HAS_DELEGATED_ACCESS_ANY = 'HAS_DELEGATED_ACCESS_ANY';
    private const HAS_DELEGATED_ACCESS_EVENTS = 'HAS_DELEGATED_ACCESS_EVENTS';
    private const HAS_DELEGATED_ACCESS_ADHERENTS = 'HAS_DELEGATED_ACCESS_ADHERENTS';
    private const HAS_DELEGATED_ACCESS_COMMITTEE = 'HAS_DELEGATED_ACCESS_COMMITTEE';
    private const HAS_DELEGATED_ACCESS_MESSAGES = 'HAS_DELEGATED_ACCESS_MESSAGES';

    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function supports($attribute, $subject)
    {
        return 0 === \strpos($attribute, 'HAS_DELEGATED_ACCESS_');
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return false;
        }

        $delegatedAccess = $this->requestStack->getCurrentRequest()->attributes->get('delegated_access');

        if (!$delegatedAccess || $delegatedAccess->getType() !== $subject) {
            return false;
        }

        switch ($attribute) {
            case self::HAS_DELEGATED_ACCESS_ANY:
                return \count($delegatedAccess->getAccesses()) > 0;
            case self::HAS_DELEGATED_ACCESS_ADHERENTS:
                return \in_array(DelegatedAccess::ACCESS_ADHERENTS, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_EVENTS:
                return \in_array(DelegatedAccess::ACCESS_EVENTS, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_COMMITTEE:
                return \in_array(DelegatedAccess::ACCESS_COMMITTEE, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_MESSAGES:
                return \in_array(DelegatedAccess::ACCESS_MESSAGES, $delegatedAccess->getAccesses(), true);
            default:
                return false;
        }
    }
}
