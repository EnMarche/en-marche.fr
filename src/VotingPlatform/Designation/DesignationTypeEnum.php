<?php

namespace App\VotingPlatform\Designation;

use MyCLabs\Enum\Enum;

final class DesignationTypeEnum extends Enum
{
    public const COMMITTEE_ADHERENT = 'committee_adherent';
    public const COMMITTEE_ADHERENT_FEMALE = 'committee_adherent_female';
    public const COMMITTEE_ADHERENT_MALE = 'committee_adherent_male';

    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const COMMITTEE_SUPERVISOR_FEMALE = 'committee_supervisor_female';
    public const COMMITTEE_SUPERVISOR_MALE = 'committee_supervisor_male';

    public const COPOL = 'copol';
    public const NATIONAL_COUNCIL = 'national_council';

    public const TITLES = [
        self::COMMITTEE_SUPERVISOR => 'Élection du binôme paritaire d’Animateurs locaux',
        self::COMMITTEE_SUPERVISOR_FEMALE => 'Élection de l’Animatrice locale du comité',
        self::COMMITTEE_SUPERVISOR_MALE => 'Élection de l’Animateur local du comité',
        self::COMMITTEE_ADHERENT => 'Désignation du binôme d’adhérents siégeant au Conseil territorial',
        self::COMMITTEE_ADHERENT_FEMALE => 'Désignation d’une adhérente siégeant au Conseil territorial',
        self::COMMITTEE_ADHERENT_MALE => 'Désignation d’un adhérent siégeant au Conseil territorial',
        self::COPOL => 'Désignation des binômes au Copol',
        self::NATIONAL_COUNCIL => 'Désignation du trinôme au Conseil national',
    ];

    public const MAIN_TYPES = [
        self::COMMITTEE_SUPERVISOR,
        self::COMMITTEE_ADHERENT,
        self::COPOL,
        self::NATIONAL_COUNCIL,
    ];
}
