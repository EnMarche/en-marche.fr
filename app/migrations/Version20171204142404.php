<?php

namespace Migrations;

use AppBundle\Migration\CitizenProjectCategorySkillMigration;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20171204142404 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_project_category_skills (id INT AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED DEFAULT NULL, skill_id INT DEFAULT NULL, promotion TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_168C868A12469DE2 (category_id), INDEX IDX_168C868A5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_project_skills (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX citizen_project_skill_slug_unique (slug), UNIQUE INDEX citizen_project_skill_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_projects_skills (citizen_project_id INT UNSIGNED NOT NULL, citizen_project_skill_id INT NOT NULL, INDEX IDX_B3D202D9B3584533 (citizen_project_id), INDEX IDX_B3D202D9EA64A9D0 (citizen_project_skill_id), PRIMARY KEY(citizen_project_id, citizen_project_skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_category_skills ADD CONSTRAINT FK_168C868A12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('ALTER TABLE citizen_project_category_skills ADD CONSTRAINT FK_168C868A5585C142 FOREIGN KEY (skill_id) REFERENCES citizen_project_skills (id)');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id)');
    }

    public function postUp(Schema $schema)
    {
        // This service will be deleted when the migration will be run on production
        if ($this->container->has(CitizenProjectCategorySkillMigration::class)) {
            $this->container->get(CitizenProjectCategorySkillMigration::class)->populate();
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE citizen_project_category_skills DROP FOREIGN KEY FK_168C868A5585C142');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('DROP TABLE citizen_project_category_skills');
        $this->addSql('DROP TABLE citizen_project_skills');
        $this->addSql('DROP TABLE citizen_projects_skills');
    }
}
