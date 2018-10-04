<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\RecipientInterface;

final class CitizenProjectCreationConfirmationMail extends AbstractCitizenProjectMail
{
    public const SUBJECT = 'Nous avons bien reçu votre demande de création de projet citoyen !';

    public static function createRecipientFrom(Adherent $adherent): RecipientInterface
    {
        return self::createRecipientFromAdherent($adherent);
    }

    public static function createTemplateVars(string $adherentName, string $citizenProjectName, string $link): array
    {
        return [
            'target_firstname' => StringCleaner::htmlspecialchars($adherentName),
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProjectName),
            'link_create_action' => $link,
        ];
    }
}
