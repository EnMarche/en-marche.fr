<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181213120824 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD votes_count INT UNSIGNED NOT NULL, ADD author_category VARCHAR(9) NOT NULL');
        $this->addSql('CREATE INDEX status_idx ON ideas_workshop_idea (status)');
        $this->addSql('CREATE INDEX author_category_idx ON ideas_workshop_idea (author_category)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX status_idx ON ideas_workshop_idea');
        $this->addSql('DROP INDEX author_category_idx ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP votes_count, DROP author_category');
    }
}
