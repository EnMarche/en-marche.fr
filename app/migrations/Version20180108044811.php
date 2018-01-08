<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180108044811 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Themes
        $this->addSql('CREATE TABLE timeline_theme_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id BIGINT DEFAULT NULL, title VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, locale VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_F81F7293989D9B62 (slug), INDEX IDX_F81F72932C2AC5D3 (translatable_id), UNIQUE INDEX UNIQ_F81F72932C2AC5D34180C698 (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timeline_theme_translations ADD CONSTRAINT FK_F81F72932C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_themes (id) ON DELETE CASCADE');
        $this->addSQL(<<<'SQL'
            INSERT INTO timeline_theme_translations
            (translatable_id, locale, title, slug, description)
            (SELECT theme.id, 'fr', theme.title, theme.slug, theme.description FROM timeline_themes theme)
SQL
        );
        $this->addSql('DROP INDEX UNIQ_8ADDB8F6989D9B62 ON timeline_themes');
        $this->addSql('ALTER TABLE timeline_themes DROP title, DROP slug, DROP description');

        // Measures
        $this->addSql('CREATE TABLE timeline_measure_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id BIGINT DEFAULT NULL, title VARCHAR(100) NOT NULL, locale VARCHAR(10) NOT NULL, INDEX IDX_5C9EB6072C2AC5D3 (translatable_id), UNIQUE INDEX UNIQ_5C9EB6072C2AC5D34180C698 (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timeline_measure_translations ADD CONSTRAINT FK_5C9EB6072C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_measures (id) ON DELETE CASCADE');
        $this->addSQL(<<<'SQL'
            INSERT INTO timeline_measure_translations
            (translatable_id, locale, title)
            (SELECT measure.id, 'fr', measure.title FROM timeline_measures measure)
SQL
        );
        $this->addSql('ALTER TABLE timeline_measures DROP title');
    }

    public function down(Schema $schema)
    {
        // Themes
        $this->addSql('ALTER TABLE timeline_themes ADD title VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, ADD slug VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, ADD description LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8ADDB8F6989D9B62 ON timeline_themes (slug)');
        $this->addSql(<<<'SQL'
            UPDATE timeline_themes theme
            INNER JOIN timeline_theme_translations translation
                ON theme.id = translation.translatable_id
                AND translation.locale = 'fr'
            SET theme.title = translation.title, theme.slug = translation.slug, theme.description = translation.description
SQL
        );
        $this->addSql('DROP TABLE timeline_theme_translations');

        // Measures
        $this->addSql('ALTER TABLE timeline_measures ADD title VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql(<<<'SQL'
            UPDATE timeline_measures measure
            INNER JOIN timeline_measure_translations translation
                ON measure.id = translation.translatable_id
                AND translation.locale = 'fr'
            SET measure.title = translation.title
SQL
        );
        $this->addSql('DROP TABLE timeline_measure_translations');
    }
}
