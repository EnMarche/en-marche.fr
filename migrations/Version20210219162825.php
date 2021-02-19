<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210219162825 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cause_follower (
          cause_id INT UNSIGNED NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          INDEX IDX_6F9A854466E2221E (cause_id), 
          INDEX IDX_6F9A854425F06C53 (adherent_id), 
          PRIMARY KEY(cause_id, adherent_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          cause_follower 
        ADD 
          CONSTRAINT FK_6F9A854466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          cause_follower 
        ADD 
          CONSTRAINT FK_6F9A854425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cause_follower');
    }
}
