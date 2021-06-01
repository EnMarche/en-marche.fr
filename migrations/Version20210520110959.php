<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210520110959 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE elected_representatives_register_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE cause_quick_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE coalition_moderator_role_association_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE committee_candidacies_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE poll_vote_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE territorial_council_candidacies_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cause (
          id SERIAL NOT NULL,
          author_id INT DEFAULT NULL,
          coalition_id INT NOT NULL,
          second_coalition_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          description TEXT DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          followers_count INT NOT NULL,
          canonical_name VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          uuid UUID NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          mailchimp_id INT DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F0DA7FBFF675F31B ON cause (author_id)');
        $this->addSql('CREATE INDEX IDX_F0DA7FBFC2A46A23 ON cause (coalition_id)');
        $this->addSql('CREATE INDEX IDX_F0DA7FBF38C2B2DC ON cause (second_coalition_id)');
        $this->addSql('CREATE UNIQUE INDEX cause_uuid_unique ON cause (uuid)');
        $this->addSql('CREATE UNIQUE INDEX cause_name_unique ON cause (name)');
        $this->addSql('COMMENT ON COLUMN cause.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE cause_follower (
          id SERIAL NOT NULL,
          cause_id INT NOT NULL,
          zone_id INT DEFAULT NULL,
          adherent_id INT DEFAULT NULL,
          first_name VARCHAR(50) DEFAULT NULL,
          email_address VARCHAR(255) DEFAULT NULL,
          cgu_accepted BOOLEAN DEFAULT NULL,
          cause_subscription BOOLEAN DEFAULT NULL,
          coalition_subscription BOOLEAN DEFAULT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          uuid UUID NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_6F9A854466E2221E ON cause_follower (cause_id)');
        $this->addSql('CREATE INDEX IDX_6F9A85449F2C3FAB ON cause_follower (zone_id)');
        $this->addSql('CREATE INDEX IDX_6F9A854425F06C53 ON cause_follower (adherent_id)');
        $this->addSql('CREATE UNIQUE INDEX cause_follower_unique ON cause_follower (cause_id, adherent_id)');
        $this->addSql('CREATE UNIQUE INDEX cause_follower_uuid_unique ON cause_follower (uuid)');
        $this->addSql('COMMENT ON COLUMN cause_follower.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE cause_quick_action (
          id INT NOT NULL,
          cause_id INT NOT NULL,
          title VARCHAR(100) NOT NULL,
          url VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_DC1B329B66E2221E ON cause_quick_action (cause_id)');
        $this->addSql('CREATE TABLE coalition (
          id SERIAL NOT NULL,
          name VARCHAR(255) NOT NULL,
          description TEXT NOT NULL,
          youtube_id VARCHAR(11) DEFAULT NULL,
          enabled BOOLEAN DEFAULT \'true\' NOT NULL,
          uuid UUID NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX coalition_uuid_unique ON coalition (uuid)');
        $this->addSql('CREATE UNIQUE INDEX coalition_name_unique ON coalition (name)');
        $this->addSql('COMMENT ON COLUMN coalition.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE coalition_follower (
          id SERIAL NOT NULL,
          coalition_id INT NOT NULL,
          adherent_id INT DEFAULT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          uuid UUID NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_DFF370E2C2A46A23 ON coalition_follower (coalition_id)');
        $this->addSql('CREATE INDEX IDX_DFF370E225F06C53 ON coalition_follower (adherent_id)');
        $this->addSql('CREATE UNIQUE INDEX coalition_follower_uuid_unique ON coalition_follower (uuid)');
        $this->addSql('COMMENT ON COLUMN coalition_follower.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE coalition_moderator_role_association (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE committee_candidacies_group (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE email_templates (
          id SERIAL NOT NULL,
          author_id INT DEFAULT NULL,
          label VARCHAR(255) NOT NULL,
          content TEXT NOT NULL,
          uuid UUID NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_6023E2A5F675F31B ON email_templates (author_id)');
        $this->addSql('CREATE UNIQUE INDEX email_template_uuid_unique ON email_templates (uuid)');
        $this->addSql('COMMENT ON COLUMN email_templates.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE event_coalition (
          event_id INT NOT NULL,
          coalition_id INT NOT NULL,
          PRIMARY KEY(event_id, coalition_id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_215844FA71F7E88B ON event_coalition (event_id)');
        $this->addSql('CREATE INDEX IDX_215844FAC2A46A23 ON event_coalition (coalition_id)');
        $this->addSql('CREATE TABLE event_cause (
          event_id INT NOT NULL,
          cause_id INT NOT NULL,
          PRIMARY KEY(event_id, cause_id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B1C1CE9371F7E88B ON event_cause (event_id)');
        $this->addSql('CREATE INDEX IDX_B1C1CE9366E2221E ON event_cause (cause_id)');
        $this->addSql('CREATE TABLE internal_api_application (
          id SERIAL NOT NULL,
          application_name VARCHAR(200) NOT NULL,
          hostname VARCHAR(200) NOT NULL,
          uuid UUID NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX internal_application_uuid_unique ON internal_api_application (uuid)');
        $this->addSql('COMMENT ON COLUMN internal_api_application.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE poll (
          id SERIAL NOT NULL,
          author_id INT DEFAULT NULL,
          zone_id INT DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          question VARCHAR(255) NOT NULL,
          finish_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          published BOOLEAN DEFAULT \'false\' NOT NULL,
          uuid UUID NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          type VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_84BCFA45F675F31B ON poll (author_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA459F2C3FAB ON poll (zone_id)');
        $this->addSql('CREATE INDEX IDX_84BCFA454B09E92C ON poll (administrator_id)');
        $this->addSql('CREATE UNIQUE INDEX poll_uuid_unique ON poll (uuid)');
        $this->addSql('COMMENT ON COLUMN poll.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE poll_choice (
          id SERIAL NOT NULL,
          poll_id INT NOT NULL,
          value VARCHAR(255) NOT NULL,
          uuid UUID NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_2DAE19C93C947C0F ON poll_choice (poll_id)');
        $this->addSql('CREATE UNIQUE INDEX poll_choice_uuid_unique ON poll_choice (uuid)');
        $this->addSql('COMMENT ON COLUMN poll_choice.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE poll_vote (
          id INT NOT NULL,
          choice_id INT NOT NULL,
          adherent_id INT DEFAULT NULL,
          device_id INT DEFAULT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_ED568EBE998666D1 ON poll_vote (choice_id)');
        $this->addSql('CREATE INDEX IDX_ED568EBE25F06C53 ON poll_vote (adherent_id)');
        $this->addSql('CREATE INDEX IDX_ED568EBE94A4C7D4 ON poll_vote (device_id)');
        $this->addSql('CREATE TABLE territorial_council_candidacies_group (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBFF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBFC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBF38C2B2DC FOREIGN KEY (second_coalition_id) REFERENCES coalition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A854466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A85449F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A854425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          cause_quick_action
        ADD
          CONSTRAINT FK_DC1B329B66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          coalition_follower
        ADD
          CONSTRAINT FK_DFF370E2C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          coalition_follower
        ADD
          CONSTRAINT FK_DFF370E225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          event_coalition
        ADD
          CONSTRAINT FK_215844FA71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          event_coalition
        ADD
          CONSTRAINT FK_215844FAC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          event_cause
        ADD
          CONSTRAINT FK_B1C1CE9371F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          event_cause
        ADD
          CONSTRAINT FK_B1C1CE9366E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll
        ADD
          CONSTRAINT FK_84BCFA45F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll
        ADD
          CONSTRAINT FK_84BCFA459F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll
        ADD
          CONSTRAINT FK_84BCFA454B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll_choice
        ADD
          CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll_vote
        ADD
          CONSTRAINT FK_ED568EBE998666D1 FOREIGN KEY (choice_id) REFERENCES poll_choice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll_vote
        ADD
          CONSTRAINT FK_ED568EBE25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          poll_vote
        ADD
          CONSTRAINT FK_ED568EBE94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE elected_representatives_register');
        $this->addSql('ALTER TABLE adherent_message_filters ADD cause_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F9466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_28CA9F9466E2221E ON adherent_message_filters (cause_id)');
        $this->addSql('ALTER TABLE adherents ADD coalition_moderator_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD source VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD coalition_subscription BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE adherents ADD cause_subscription BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE adherents ADD coalitions_cgu_accepted BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA38828ED30 FOREIGN KEY (coalition_moderator_role_id) REFERENCES coalition_moderator_role_association (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA38828ED30 ON adherents (coalition_moderator_role_id)');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT fk_9a04454a35d7af0');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT fk_9a044548d4924c4');
        $this->addSql('DROP INDEX uniq_9a04454a35d7af0');
        $this->addSql('DROP INDEX uniq_9a044548d4924c4');
        $this->addSql('ALTER TABLE committee_candidacy ADD candidacies_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE committee_candidacy DROP invitation_id');
        $this->addSql('ALTER TABLE committee_candidacy DROP binome_id');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A04454FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES committee_candidacies_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9A04454FC1537C1 ON committee_candidacy (candidacies_group_id)');
        $this->addSql('ALTER TABLE committee_candidacy_invitation ADD candidacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          committee_candidacy_invitation
        ADD
          CONSTRAINT FK_368B016159B22434 FOREIGN KEY (candidacy_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_368B016159B22434 ON committee_candidacy_invitation (candidacy_id)');
        $this->addSql('ALTER TABLE committees ADD closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE designation ADD pools TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE designation ADD description TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN designation.pools IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE events ADD visio_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD interests TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD mode VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN events.interests IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE geo_borough ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_borough ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_borough.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_borough.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_canton ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_canton ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_canton.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_canton.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_city ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_city ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_city.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_city.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_city_community ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_city_community ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_city_community.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_city_community.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_consular_district ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_consular_district ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_consular_district.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_consular_district.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_country ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_country ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_country.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_country.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_custom_zone ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_custom_zone ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_custom_zone.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_custom_zone.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_department ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_department ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_department.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_department.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_district ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_district ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_district.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_district.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_foreign_district ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_foreign_district ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_foreign_district.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_foreign_district.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_region ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_region ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_region.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_region.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE geo_zone ADD uuid UUID NOT NULL');
        $this->addSql('ALTER TABLE geo_zone ADD postal_code TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_zone ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_zone ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN geo_zone.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN geo_zone.postal_code IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN geo_zone.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN geo_zone.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE jecoute_data_survey ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey ADD longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN jecoute_data_survey.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN jecoute_data_survey.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE jecoute_news ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD notification BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD published BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD space VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news ALTER topic DROP NOT NULL');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_3436209F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3436209F675F31B ON jecoute_news (author_id)');
        $this->addSql('ALTER TABLE oauth_clients ADD requested_roles TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN oauth_clients.requested_roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT fk_39885b6a35d7af0');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT fk_39885b68d4924c4');
        $this->addSql('DROP INDEX uniq_39885b68d4924c4');
        $this->addSql('DROP INDEX uniq_39885b6a35d7af0');
        $this->addSql('ALTER TABLE territorial_council_candidacy ADD candidacies_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP invitation_id');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP binome_id');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT FK_39885B6FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES territorial_council_candidacies_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_39885B6FC1537C1 ON territorial_council_candidacy (candidacies_group_id)');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation ADD candidacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        ADD
          CONSTRAINT FK_DA86009A59B22434 FOREIGN KEY (candidacy_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DA86009A59B22434 ON territorial_council_candidacy_invitation (candidacy_id)');
        $this->addSql('ALTER TABLE territorial_council_election DROP questions');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F9466E2221E');
        $this->addSql('ALTER TABLE cause_follower DROP CONSTRAINT FK_6F9A854466E2221E');
        $this->addSql('ALTER TABLE cause_quick_action DROP CONSTRAINT FK_DC1B329B66E2221E');
        $this->addSql('ALTER TABLE event_cause DROP CONSTRAINT FK_B1C1CE9366E2221E');
        $this->addSql('ALTER TABLE cause DROP CONSTRAINT FK_F0DA7FBFC2A46A23');
        $this->addSql('ALTER TABLE cause DROP CONSTRAINT FK_F0DA7FBF38C2B2DC');
        $this->addSql('ALTER TABLE coalition_follower DROP CONSTRAINT FK_DFF370E2C2A46A23');
        $this->addSql('ALTER TABLE event_coalition DROP CONSTRAINT FK_215844FAC2A46A23');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA38828ED30');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT FK_9A04454FC1537C1');
        $this->addSql('ALTER TABLE poll_choice DROP CONSTRAINT FK_2DAE19C93C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP CONSTRAINT FK_ED568EBE998666D1');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT FK_39885B6FC1537C1');
        $this->addSql('DROP SEQUENCE cause_quick_action_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE coalition_moderator_role_association_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE committee_candidacies_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE poll_vote_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE territorial_council_candidacies_group_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE elected_representatives_register_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE elected_representatives_register (
          id SERIAL NOT NULL,
          adherent_id INT DEFAULT NULL,
          department_id INT DEFAULT NULL,
          commune_id INT DEFAULT NULL,
          type_elu VARCHAR(255) DEFAULT NULL,
          dpt VARCHAR(255) DEFAULT NULL,
          dpt_nom VARCHAR(255) DEFAULT NULL,
          nom VARCHAR(255) DEFAULT NULL,
          prenom VARCHAR(255) DEFAULT NULL,
          genre VARCHAR(255) DEFAULT NULL,
          date_naissance TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          code_profession BIGINT DEFAULT NULL,
          nom_profession TEXT DEFAULT NULL,
          date_debut_mandat TEXT DEFAULT NULL,
          nom_fonction VARCHAR(255) DEFAULT NULL,
          date_debut_fonction TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          nuance_politique VARCHAR(255) DEFAULT NULL,
          identification_elu BIGINT DEFAULT NULL,
          nationalite_elu VARCHAR(255) DEFAULT NULL,
          epci_siren BIGINT DEFAULT NULL,
          epci_nom VARCHAR(255) DEFAULT NULL,
          commune_dpt BIGINT DEFAULT NULL,
          commune_code BIGINT DEFAULT NULL,
          commune_nom VARCHAR(255) DEFAULT NULL,
          commune_population BIGINT DEFAULT NULL,
          canton_code BIGINT DEFAULT NULL,
          canton_nom VARCHAR(255) DEFAULT NULL,
          region_code VARCHAR(255) DEFAULT NULL,
          region_nom VARCHAR(255) DEFAULT NULL,
          euro_code BIGINT DEFAULT NULL,
          euro_nom VARCHAR(255) DEFAULT NULL,
          circo_legis_code BIGINT DEFAULT NULL,
          circo_legis_nom VARCHAR(255) DEFAULT NULL,
          infos_supp TEXT DEFAULT NULL,
          uuid VARCHAR(36) DEFAULT NULL,
          nb_participation_events INT DEFAULT NULL,
          adherent_uuid UUID DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_55314f9525f06c53 ON elected_representatives_register (adherent_id)');
        $this->addSql('COMMENT ON COLUMN elected_representatives_register.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE
          elected_representatives_register
        ADD
          CONSTRAINT fk_55314f9525f06c53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE cause');
        $this->addSql('DROP TABLE cause_follower');
        $this->addSql('DROP TABLE cause_quick_action');
        $this->addSql('DROP TABLE coalition');
        $this->addSql('DROP TABLE coalition_follower');
        $this->addSql('DROP TABLE coalition_moderator_role_association');
        $this->addSql('DROP TABLE committee_candidacies_group');
        $this->addSql('DROP TABLE email_templates');
        $this->addSql('DROP TABLE event_coalition');
        $this->addSql('DROP TABLE event_cause');
        $this->addSql('DROP TABLE internal_api_application');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE poll_choice');
        $this->addSql('DROP TABLE poll_vote');
        $this->addSql('DROP TABLE territorial_council_candidacies_group');
        $this->addSql('DROP INDEX UNIQ_562C7DA38828ED30');
        $this->addSql('ALTER TABLE adherents DROP coalition_moderator_role_id');
        $this->addSql('ALTER TABLE adherents DROP source');
        $this->addSql('ALTER TABLE adherents DROP coalition_subscription');
        $this->addSql('ALTER TABLE adherents DROP cause_subscription');
        $this->addSql('ALTER TABLE adherents DROP coalitions_cgu_accepted');
        $this->addSql('ALTER TABLE geo_zone DROP uuid');
        $this->addSql('ALTER TABLE geo_zone DROP postal_code');
        $this->addSql('ALTER TABLE geo_zone DROP latitude');
        $this->addSql('ALTER TABLE geo_zone DROP longitude');
        $this->addSql('ALTER TABLE committees DROP closed_at');
        $this->addSql('ALTER TABLE events DROP visio_url');
        $this->addSql('ALTER TABLE events DROP interests');
        $this->addSql('ALTER TABLE events DROP mode');
        $this->addSql('ALTER TABLE events DROP image_name');
        $this->addSql('ALTER TABLE geo_region DROP latitude');
        $this->addSql('ALTER TABLE geo_region DROP longitude');
        $this->addSql('ALTER TABLE jecoute_news DROP CONSTRAINT FK_3436209F675F31B');
        $this->addSql('DROP INDEX IDX_3436209F675F31B');
        $this->addSql('ALTER TABLE jecoute_news DROP author_id');
        $this->addSql('ALTER TABLE jecoute_news DROP notification');
        $this->addSql('ALTER TABLE jecoute_news DROP published');
        $this->addSql('ALTER TABLE jecoute_news DROP space');
        $this->addSql('ALTER TABLE jecoute_news ALTER topic SET NOT NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP latitude');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP longitude');
        $this->addSql('ALTER TABLE geo_country DROP latitude');
        $this->addSql('ALTER TABLE geo_country DROP longitude');
        $this->addSql('ALTER TABLE geo_foreign_district DROP latitude');
        $this->addSql('ALTER TABLE geo_foreign_district DROP longitude');
        $this->addSql('ALTER TABLE geo_consular_district DROP latitude');
        $this->addSql('ALTER TABLE geo_consular_district DROP longitude');
        $this->addSql('ALTER TABLE geo_department DROP latitude');
        $this->addSql('ALTER TABLE geo_department DROP longitude');
        $this->addSql('ALTER TABLE geo_canton DROP latitude');
        $this->addSql('ALTER TABLE geo_canton DROP longitude');
        $this->addSql('ALTER TABLE geo_district DROP latitude');
        $this->addSql('ALTER TABLE geo_district DROP longitude');
        $this->addSql('ALTER TABLE geo_city DROP latitude');
        $this->addSql('ALTER TABLE geo_city DROP longitude');
        $this->addSql('ALTER TABLE geo_borough DROP latitude');
        $this->addSql('ALTER TABLE geo_borough DROP longitude');
        $this->addSql('ALTER TABLE geo_custom_zone DROP latitude');
        $this->addSql('ALTER TABLE geo_custom_zone DROP longitude');
        $this->addSql('ALTER TABLE geo_city_community DROP latitude');
        $this->addSql('ALTER TABLE geo_city_community DROP longitude');
        $this->addSql('ALTER TABLE designation DROP pools');
        $this->addSql('ALTER TABLE designation DROP description');
        $this->addSql('DROP INDEX IDX_9A04454FC1537C1');
        $this->addSql('ALTER TABLE committee_candidacy ADD invitation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE committee_candidacy ADD binome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE committee_candidacy DROP candidacies_group_id');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT fk_9a04454a35d7af0 FOREIGN KEY (invitation_id) REFERENCES committee_candidacy_invitation (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT fk_9a044548d4924c4 FOREIGN KEY (binome_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_9a04454a35d7af0 ON committee_candidacy (invitation_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_9a044548d4924c4 ON committee_candidacy (binome_id)');
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP CONSTRAINT FK_368B016159B22434');
        $this->addSql('DROP INDEX IDX_368B016159B22434');
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP candidacy_id');
        $this->addSql('DROP INDEX IDX_28CA9F9466E2221E');
        $this->addSql('ALTER TABLE adherent_message_filters DROP cause_id');
        $this->addSql('ALTER TABLE oauth_clients DROP requested_roles');
        $this->addSql('ALTER TABLE territorial_council_election ADD questions TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP CONSTRAINT FK_DA86009A59B22434');
        $this->addSql('DROP INDEX IDX_DA86009A59B22434');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP candidacy_id');
        $this->addSql('DROP INDEX IDX_39885B6FC1537C1');
        $this->addSql('ALTER TABLE territorial_council_candidacy ADD invitation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE territorial_council_candidacy ADD binome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP candidacies_group_id');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT fk_39885b6a35d7af0 FOREIGN KEY (invitation_id) REFERENCES territorial_council_candidacy_invitation (id) ON DELETE
        SET
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy
        ADD
          CONSTRAINT fk_39885b68d4924c4 FOREIGN KEY (binome_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_39885b68d4924c4 ON territorial_council_candidacy (binome_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_39885b6a35d7af0 ON territorial_council_candidacy (invitation_id)');
    }
}
