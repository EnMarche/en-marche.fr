<?php

namespace App\TerritorialCouncil\Candidacy;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\ValueObject\Genders;

class NationalCouncilCandidacyConfigurator
{
    public static function getAvailableGenders(Candidacy $candidacy): array
    {
        /** @var TerritorialCouncil $coTerr */
        $coTerr = $candidacy->getElection()->getTerritorialCouncil();

        $members = [$candidacy];

        if ($president = $coTerr->getMemberships()->getPresident()) {
            $members[] = $president->getAdherent();
        }

        $config = [
            Genders::MALE => 2,
            Genders::FEMALE => 2,
        ];

        foreach ($members as $member) {
            if ($member->isFemale()) {
                --$config[Genders::FEMALE];
            } else {
                --$config[Genders::MALE];
            }
        }

        return $config;
    }

    public static function getNeededQualitiesForCandidacy(Candidacy $candidacy): array
    {
        return [
            [TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT],
            [TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR],
            array_diff(TerritorialCouncilQualityEnum::ABLE_TO_CANDIDATE, [
                TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
                TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
            ]),
        ];
    }
}
