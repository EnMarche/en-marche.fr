<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190513142804 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests CHANGE birth_city birth_city VARCHAR(50) NOT NULL, 
          CHANGE city city VARCHAR(50) NOT NULL, 
          CHANGE vote_city vote_city VARCHAR(50) NOT NULL, 
          CHANGE assessor_city assessor_city VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          assessor_requests CHANGE birth_city birth_city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE city city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE vote_city vote_city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
          CHANGE assessor_city assessor_city VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
