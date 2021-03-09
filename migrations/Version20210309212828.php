<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210309212828 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation ADD candidacy_id INT NOT NULL');

        $this->addSql(
            'UPDATE territorial_council_candidacy_invitation AS t1
            INNER JOIN territorial_council_candidacy AS t2 ON t2.invitation_id = t1.id
            SET t1.candidacy_id = t2.id'
        );

        $this->addSql('ALTER TABLE 
          territorial_council_candidacy_invitation 
        ADD 
          CONSTRAINT FK_DA86009A59B22434 FOREIGN KEY (candidacy_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DA86009A59B22434 ON territorial_council_candidacy_invitation (candidacy_id)');

        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6A35D7AF0');
        $this->addSql('DROP INDEX UNIQ_39885B6A35D7AF0 ON territorial_council_candidacy');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP invitation_id');

        $this->addSql('ALTER TABLE committee_candidacy_invitation ADD candidacy_id INT NOT NULL');

        $this->addSql(
            'UPDATE committee_candidacy_invitation AS t1
            INNER JOIN committee_candidacy AS t2 ON t2.invitation_id = t1.id
            SET t1.candidacy_id = t2.id'
        );
        $this->addSql('ALTER TABLE 
          committee_candidacy_invitation 
        ADD 
          CONSTRAINT FK_368B016159B22434 FOREIGN KEY (candidacy_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_368B016159B22434 ON committee_candidacy_invitation (candidacy_id)');

        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454A35D7AF0');
        $this->addSql('DROP INDEX UNIQ_9A04454A35D7AF0 ON committee_candidacy');
        $this->addSql('ALTER TABLE committee_candidacy DROP invitation_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy ADD invitation_id INT UNSIGNED DEFAULT NULL');

        $this->addSql(
            'UPDATE committee_candidacy AS t1
            INNER JOIN committee_candidacy_invitation AS t2 ON t2.candidacy_id = t1.id
            SET t1.invitation_id = t2.id'
        );

        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A04454A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES committee_candidacy_invitation (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A04454A35D7AF0 ON committee_candidacy (invitation_id)');

        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP FOREIGN KEY FK_368B016159B22434');
        $this->addSql('DROP INDEX IDX_368B016159B22434 ON committee_candidacy_invitation');
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP candidacy_id');

        $this->addSql('ALTER TABLE territorial_council_candidacy ADD invitation_id INT UNSIGNED DEFAULT NULL');

        $this->addSql(
            'UPDATE territorial_council_candidacy AS t1
            INNER JOIN territorial_council_candidacy_invitation AS t2 ON t2.candidacy_id = t1.id
            SET t1.invitation_id = t2.id'
        );

        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES territorial_council_candidacy_invitation (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39885B6A35D7AF0 ON territorial_council_candidacy (invitation_id)');

        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP FOREIGN KEY FK_DA86009A59B22434');
        $this->addSql('DROP INDEX IDX_DA86009A59B22434 ON territorial_council_candidacy_invitation');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP candidacy_id');
    }
}
