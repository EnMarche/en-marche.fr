<?php

namespace App\Mailer\Message;

use App\Entity\Projection\ManagedUser;
use App\Referent\ReferentMessage as ReferentMessageModel;
use Ramsey\Uuid\Uuid;

final class ReferentMessage extends Message
{
    /**
     * @param ManagedUser[] $recipients
     *
     * @return ReferentMessage
     */
    public static function createFromModel(ReferentMessageModel $model, array $recipients): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $referent = $model->getFrom();
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmail(),
            $first->getFullName() ?: '',
            $model->getSubject(),
            [
                'referant_firstname' => self::escape($referent->getFullName()),
                'target_message' => $model->getContent(),
            ],
            [
                'target_firstname' => self::escape($first->getFirstName() ?: ''),
            ],
            $referent->getEmailAddress()
        );

        $message->setSenderEmail('jemarche@en-marche.fr');
        $message->setSenderName($referent->getFullName());

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmail(),
                $recipient->getFullName() ?: '',
                [
                    'target_firstname' => self::escape($recipient->getFirstName() ?: ''),
                ]
            );
        }

        return $message;
    }
}
