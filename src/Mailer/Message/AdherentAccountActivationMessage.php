<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentAccountActivationMessage extends Message
{
    public static function createFromAdherent(Adherent $adherent, string $confirmationLink): self
    {
        return new self(
            Uuid::uuid4(),
            '292269',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Confirmez votre compte En-Marche.fr',
            static::getTemplateVars(),
            static::getRecipientVars($adherent->getFirstName(), $confirmationLink)
        );
    }

    private static function getTemplateVars(): array
    {
        return [
            'first_name' => '',
            'activation_link' => '',
        ];
    }

    private static function getRecipientVars(string $firstName, string $confirmationLink): array
    {
        return [
            'first_name' => self::escape($firstName),
            'activation_link' => $confirmationLink,
        ];
    }
}
