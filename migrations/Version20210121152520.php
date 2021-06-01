<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210121152520 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION postgis;');
    }

    public function down(Schema $schema): void
    {
    }
}
