<?php

namespace App\Validator\TerritorialCouncil;

use App\Entity\TerritorialCouncil\CandidacyInvitation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidTerritorialCouncilCandidacyForCopolInvitationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidTerritorialCouncilCandidacyForCopolInvitation) {
            throw new UnexpectedTypeException($constraint, ValidTerritorialCouncilCandidacyForCopolInvitation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof CandidacyInvitation) {
            throw new UnexpectedValueException($value, CandidacyInvitation::class);
        }

        if (!$invitedMembership = $value->getMembership()) {
            return;
        }

        $candidacy = $value->getCandidacy();
        $invitedMembership = $value->getMembership();

        if ($candidacy->getGender() === $invitedMembership->getAdherent()->getGender()) {
            $this->context
                ->buildViolation($constraint->messageInvalidGender)
                ->atPath('membership')
                ->addViolation()
            ;

            return;
        }

        if ($invitedMembership->hasForbiddenForCandidacyQuality()) {
            $this->context
                ->buildViolation($constraint->messageMembershipNotAvailable)
                ->atPath('membership')
                ->addViolation()
            ;

            return;
        }

        $invitedCandidacy = $invitedMembership->getCandidacyForElection($candidacy->getElection());

        if ($invitedCandidacy && $invitedCandidacy->isConfirmed()) {
            $this->context
                ->buildViolation($constraint->messageMembershipAlreadyCandidate)
                ->atPath('membership')
                ->addViolation()
            ;

            return;
        }

        if (!\in_array($candidacy->getQuality(), $invitedMembership->getQualityNames(), true)) {
            $this->context
                ->buildViolation($constraint->messageInvalidQuality)
                ->atPath('membership')
                ->addViolation()
            ;

            return;
        }
    }
}
