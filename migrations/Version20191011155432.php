<?php

namespace Migrations;

use App\Subscription\SubscriptionTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191011155432 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails nationaux',
            SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir la newsletter hebdomadaire nationale',
            SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de mon/ma candidat(e) aux municipales 2020',
            'municipal_email'
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de mon/ma député(e)',
            SubscriptionTypeEnum::DEPUTY_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de mon animateur(trice) local(e) de comité',
            SubscriptionTypeEnum::LOCAL_HOST_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de mon/ma référent(e) territorial(e)',
            SubscriptionTypeEnum::REFERENT_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de mon porteur de projet',
            'citizen_project_host_email'
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les informations du mouvement',
            SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir la newsletter hebdomadaire de LaREM',
            SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de votre candidat aux municipales 2020',
            'municipal_email'
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de votre député(e)',
            SubscriptionTypeEnum::DEPUTY_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de votre animateur local',
            SubscriptionTypeEnum::LOCAL_HOST_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de votre référent territorial',
            SubscriptionTypeEnum::REFERENT_EMAIL
        ));

        $this->addSql(sprintf(
            "UPDATE subscription_type SET label = '%s' WHERE code = '%s'",
            'Recevoir les e-mails de votre porteur de projet',
            'citizen_project_host_email'
        ));
    }
}
