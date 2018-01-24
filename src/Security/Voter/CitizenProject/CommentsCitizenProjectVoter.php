<?php

namespace AppBundle\Security\Voter\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommentsCitizenProjectVoter extends AbstractAdherentVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $citizenProject)
    {
        return $citizenProject instanceof CitizenProject
            && in_array($attribute, CitizenProjectPermissions::COMMENTS, true)
        ;
    }

    /**
     * @param string         $attribute
     * @param CitizenProject $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject->isApproved()) {
            return false;
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    /**
     * @param string         $attribute
     * @param Adherent       $adherent
     * @param CitizenProject $citizenProject
     *
     * @return bool
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $citizenProject): bool
    {
        return (bool) $adherent->getCitizenProjectMembershipFor($citizenProject);
    }
}
