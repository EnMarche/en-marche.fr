<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentSpaceAccessInformation;
use AppBundle\Repository\ReferentSpaceAccessInformationRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AdherentRuntime implements RuntimeExtensionInterface
{
    private $memberInterests;
    private $accessInformationRepository;

    public function __construct(ReferentSpaceAccessInformationRepository $accessInformationRepository, array $interests)
    {
        $this->accessInformationRepository = $accessInformationRepository;
        $this->memberInterests = $interests;
    }

    public function getMemberInterestLabel(string $interest)
    {
        if (!isset($this->memberInterests[$interest])) {
            return '';
        }

        return $this->memberInterests[$interest];
    }

    public function getUserLevelLabel(Adherent $adherent): string
    {
        if (!$adherent->isAdherent()) {
            return 'Non-adhérent(e)';
        }

        if ($adherent->isReferent()) {
            return $adherent->isFemale() ? 'Référente 🥇' : 'Référent 🥇';
        }

        if ($adherent->isCoReferent()) {
            return $adherent->isFemale() ? 'Co-Référente 🥇' : 'Co-Référent 🥇';
        }

        if ($adherent->isDeputy()) {
            return $adherent->isFemale() ? 'Députée 🏛' : 'Député 🏛';
        }

        if ($adherent->isSupervisor()) {
            return $adherent->isFemale() ? 'Animatrice 🏅' : 'Animateur 🏅';
        }

        if ($adherent->isHost()) {
            return $adherent->isFemale() ? 'Co-animatrice 🏅' : 'Co-animateur 🏅';
        }

        // It means the user is an adherent
        return $adherent->isFemale() ? 'Adhérente 😍' : 'Adhérent 😍';
    }

    public function getReferentPreviousVisitDate(Adherent $adherent, string $format = 'd/m/Y'): string
    {
        /** @var ReferentSpaceAccessInformation $accessInformation */
        $accessInformation = $this->accessInformationRepository->findByAdherent($adherent, 7200);

        if ($accessInformation) {
            return $accessInformation->getPreviousDate()->format($format);
        }

        return '';
    }
}
