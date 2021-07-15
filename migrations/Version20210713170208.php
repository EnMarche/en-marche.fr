<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210713170208 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE scopes (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(100) NOT NULL,
          features LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
          apps LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
          UNIQUE INDEX scope_code_unique (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE scopes');
    }
}
