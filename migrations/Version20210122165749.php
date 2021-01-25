<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210122165749 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donation_transactions (
          id SERIAL NOT NULL, 
          donation_id INT NOT NULL, 
          paybox_result_code VARCHAR(100) DEFAULT NULL, 
          paybox_authorization_code VARCHAR(100) DEFAULT NULL, 
          paybox_payload JSON DEFAULT NULL, 
          paybox_date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          paybox_transaction_id VARCHAR(255) DEFAULT NULL, 
          paybox_subscription_id VARCHAR(255) DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_89D6D36B5A4036C7 ON donation_transactions (paybox_transaction_id)');
        $this->addSql('CREATE INDEX IDX_89D6D36B4DC1279C ON donation_transactions (donation_id)');
        $this->addSql('CREATE INDEX donation_transactions_result_idx ON donation_transactions (paybox_result_code)');
        $this->addSql('COMMENT ON COLUMN donation_transactions.paybox_payload IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN donation_transactions.paybox_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN donation_transactions.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE donators (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          last_successful_donation_id INT DEFAULT NULL, 
          reference_donation_id INT DEFAULT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          last_name VARCHAR(50) DEFAULT NULL, 
          first_name VARCHAR(100) DEFAULT NULL, 
          city VARCHAR(50) DEFAULT NULL, 
          country VARCHAR(2) NOT NULL, 
          email_address VARCHAR(255) DEFAULT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          comment TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A902FDD725F06C53 ON donators (adherent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A902FDD7DE59CB1A ON donators (last_successful_donation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A902FDD7ABF665A8 ON donators (reference_donation_id)');
        $this->addSql('CREATE INDEX IDX_A902FDD7B08E074EA9D1C132C808BA5A ON donators (
          email_address, first_name, last_name
        )');
        $this->addSql('CREATE UNIQUE INDEX donator_identifier_unique ON donators (identifier)');
        $this->addSql('CREATE TABLE donator_donator_tag (
          donator_id INT NOT NULL, 
          donator_tag_id INT NOT NULL, 
          PRIMARY KEY(donator_id, donator_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_6BAEC28C831BACAF ON donator_donator_tag (donator_id)');
        $this->addSql('CREATE INDEX IDX_6BAEC28C71F026E6 ON donator_donator_tag (donator_tag_id)');
        $this->addSql('CREATE TABLE adherents (
          id SERIAL NOT NULL, 
          legislative_candidate_managed_district_id INT DEFAULT NULL, 
          managed_area_id INT DEFAULT NULL, 
          coordinator_citizen_project_area_id INT DEFAULT NULL, 
          coordinator_committee_area_id INT DEFAULT NULL, 
          procuration_managed_area_id INT DEFAULT NULL, 
          assessor_managed_area_id INT DEFAULT NULL, 
          assessor_role_id INT DEFAULT NULL, 
          municipal_manager_role_id INT DEFAULT NULL, 
          municipal_manager_supervisor_role_id INT DEFAULT NULL, 
          jecoute_managed_area_id INT DEFAULT NULL, 
          managed_district_id INT DEFAULT NULL, 
          media_id BIGINT DEFAULT NULL, 
          municipal_chief_managed_area_id INT DEFAULT NULL, 
          senatorial_candidate_managed_area_id INT DEFAULT NULL, 
          lre_area_id INT DEFAULT NULL, 
          candidate_managed_area_id INT DEFAULT NULL, 
          senator_area_id INT DEFAULT NULL, 
          consular_managed_area_id INT DEFAULT NULL, 
          nickname VARCHAR(25) DEFAULT NULL, 
          nickname_used BOOLEAN DEFAULT \'false\' NOT NULL, 
          password VARCHAR(255) DEFAULT NULL, 
          old_password VARCHAR(255) DEFAULT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          custom_gender VARCHAR(80) DEFAULT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          birthdate DATE DEFAULT NULL, 
          position VARCHAR(255) DEFAULT NULL, 
          status VARCHAR(10) DEFAULT \'DISABLED\' NOT NULL, 
          registered_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          activated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          membership_reminded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          last_logged_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          interests TEXT DEFAULT NULL, 
          adherent BOOLEAN DEFAULT \'false\' NOT NULL, 
          local_host_emails_subscription BOOLEAN DEFAULT \'false\' NOT NULL, 
          emails_subscriptions TEXT DEFAULT NULL, 
          com_mobile BOOLEAN DEFAULT NULL, 
          remind_sent BOOLEAN DEFAULT \'false\' NOT NULL, 
          comments_cgu_accepted BOOLEAN DEFAULT \'false\' NOT NULL, 
          mandates TEXT DEFAULT NULL, 
          display_media BOOLEAN NOT NULL, 
          description TEXT DEFAULT NULL, 
          facebook_page_url VARCHAR(255) DEFAULT NULL, 
          twitter_page_url VARCHAR(255) DEFAULT NULL, 
          linkedin_page_url VARCHAR(255) DEFAULT NULL, 
          telegram_page_url VARCHAR(255) DEFAULT NULL, 
          job VARCHAR(255) DEFAULT NULL, 
          activity_area VARCHAR(255) DEFAULT NULL, 
          nationality VARCHAR(2) DEFAULT NULL, 
          canary_tester BOOLEAN DEFAULT \'false\' NOT NULL, 
          email_unsubscribed BOOLEAN DEFAULT \'false\' NOT NULL, 
          email_unsubscribed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          print_privilege BOOLEAN DEFAULT \'false\' NOT NULL, 
          election_results_reporter BOOLEAN DEFAULT \'false\' NOT NULL, 
          certified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          notified_for_election BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A188FE64 ON adherents (nickname)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA39BF75CAD ON adherents (
          legislative_candidate_managed_district_id
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3DC184E71 ON adherents (managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA37034326B ON adherents (coordinator_citizen_project_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA31A912B27 ON adherents (coordinator_committee_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA339054338 ON adherents (procuration_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3E1B55931 ON adherents (assessor_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3E4A5D7A5 ON adherents (assessor_role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA379DE69AA ON adherents (municipal_manager_role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA39801977F ON adherents (municipal_manager_supervisor_role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA394E3BB99 ON adherents (jecoute_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A132C3C5 ON adherents (managed_district_id)');
        $this->addSql('CREATE INDEX IDX_562C7DA3EA9FDD75 ON adherents (media_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3CC72679B ON adherents (municipal_chief_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3FCCAF6D5 ON adherents (senatorial_candidate_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA379645AD5 ON adherents (lre_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA37657F304 ON adherents (candidate_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA393494FA8 ON adherents (senator_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3122E5FF4 ON adherents (consular_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX adherents_uuid_unique ON adherents (uuid)');
        $this->addSql('CREATE UNIQUE INDEX adherents_email_address_unique ON adherents (email_address)');
        $this->addSql('COMMENT ON COLUMN adherents.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN adherents.interests IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherents.emails_subscriptions IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherents.mandates IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherents.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN adherents.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN adherents.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE adherent_subscription_type (
          adherent_id INT NOT NULL, 
          subscription_type_id INT NOT NULL, 
          PRIMARY KEY(
            adherent_id, subscription_type_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_F93DC28A25F06C53 ON adherent_subscription_type (adherent_id)');
        $this->addSql('CREATE INDEX IDX_F93DC28AB6596C08 ON adherent_subscription_type (subscription_type_id)');
        $this->addSql('CREATE TABLE adherent_adherent_tag (
          adherent_id INT NOT NULL, 
          adherent_tag_id INT NOT NULL, 
          PRIMARY KEY(adherent_id, adherent_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_DD297F8225F06C53 ON adherent_adherent_tag (adherent_id)');
        $this->addSql('CREATE INDEX IDX_DD297F82AED03543 ON adherent_adherent_tag (adherent_tag_id)');
        $this->addSql('CREATE TABLE adherent_thematic_community (
          adherent_id INT NOT NULL, 
          thematic_community_id INT NOT NULL, 
          PRIMARY KEY(
            adherent_id, thematic_community_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_DAB0B4EC25F06C53 ON adherent_thematic_community (adherent_id)');
        $this->addSql('CREATE INDEX IDX_DAB0B4EC1BE5825E ON adherent_thematic_community (thematic_community_id)');
        $this->addSql('CREATE TABLE adherent_referent_tag (
          adherent_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(adherent_id, referent_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_79E8AFFD25F06C53 ON adherent_referent_tag (adherent_id)');
        $this->addSql('CREATE INDEX IDX_79E8AFFD9C262DB3 ON adherent_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE adherent_zone (
          adherent_id INT NOT NULL, 
          zone_id INT NOT NULL, 
          PRIMARY KEY(adherent_id, zone_id)
        )');
        $this->addSql('CREATE INDEX IDX_1C14D08525F06C53 ON adherent_zone (adherent_id)');
        $this->addSql('CREATE INDEX IDX_1C14D0859F2C3FAB ON adherent_zone (zone_id)');
        $this->addSql('CREATE TABLE donations (
          id SERIAL NOT NULL, 
          donator_id INT NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          amount INT NOT NULL, 
          donated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          last_success_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          duration SMALLINT DEFAULT 0 NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          client_ip VARCHAR(50) DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          subscription_ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          status VARCHAR(25) NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          paybox_order_ref VARCHAR(255) DEFAULT NULL, 
          check_number VARCHAR(255) DEFAULT NULL, 
          transfer_number VARCHAR(255) DEFAULT NULL, 
          nationality VARCHAR(2) DEFAULT NULL, 
          code VARCHAR(6) DEFAULT NULL, 
          filename VARCHAR(255) DEFAULT NULL, 
          comment TEXT DEFAULT NULL, 
          beneficiary VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CDE98962831BACAF ON donations (donator_id)');
        $this->addSql('CREATE INDEX donation_uuid_idx ON donations (uuid)');
        $this->addSql('CREATE INDEX donation_duration_idx ON donations (duration)');
        $this->addSql('CREATE INDEX donation_status_idx ON donations (status)');
        $this->addSql('COMMENT ON COLUMN donations.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN donations.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN donations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN donations.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN donations.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE donation_donation_tag (
          donation_id INT NOT NULL, 
          donation_tag_id INT NOT NULL, 
          PRIMARY KEY(donation_id, donation_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_F2D7087F4DC1279C ON donation_donation_tag (donation_id)');
        $this->addSql('CREATE INDEX IDX_F2D7087F790547EA ON donation_donation_tag (donation_tag_id)');
        $this->addSql('CREATE TABLE donator_tags (
          id SERIAL NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          color VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX donator_tag_label_unique ON donator_tags (label)');
        $this->addSql('CREATE TABLE donator_kinship (
          id SERIAL NOT NULL, 
          donator_id INT NOT NULL, 
          related_id INT NOT NULL, 
          kinship VARCHAR(100) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_E542211D831BACAF ON donator_kinship (donator_id)');
        $this->addSql('CREATE INDEX IDX_E542211D4162C001 ON donator_kinship (related_id)');
        $this->addSql('CREATE TABLE donator_identifier (
          id SERIAL NOT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE board_member (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          area VARCHAR(50) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DCFABEDF25F06C53 ON board_member (adherent_id)');
        $this->addSql('CREATE TABLE board_member_roles (
          board_member_id INT NOT NULL, 
          role_id INT NOT NULL, 
          PRIMARY KEY(board_member_id, role_id)
        )');
        $this->addSql('CREATE INDEX IDX_1DD1E043C7BA2FD5 ON board_member_roles (board_member_id)');
        $this->addSql('CREATE INDEX IDX_1DD1E043D60322AC ON board_member_roles (role_id)');
        $this->addSql('CREATE TABLE saved_board_members (
          board_member_owner_id INT NOT NULL, 
          board_member_saved_id INT NOT NULL, 
          PRIMARY KEY(
            board_member_owner_id, board_member_saved_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_32865A32FDCCD727 ON saved_board_members (board_member_owner_id)');
        $this->addSql('CREATE INDEX IDX_32865A324821D202 ON saved_board_members (board_member_saved_id)');
        $this->addSql('CREATE TABLE roles (
          id SERIAL NOT NULL, 
          code VARCHAR(20) NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX board_member_role_code_unique ON roles (code)');
        $this->addSql('CREATE UNIQUE INDEX board_member_role_name_unique ON roles (name)');
        $this->addSql('CREATE TABLE subscription_type (
          id SERIAL NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          external_id VARCHAR(64) DEFAULT NULL, 
          position SMALLINT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBE2473777153098 ON subscription_type (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBE247379F75D7B0 ON subscription_type (external_id)');
        $this->addSql('CREATE INDEX IDX_BBE2473777153098 ON subscription_type (code)');
        $this->addSql('CREATE TABLE districts (
          id SERIAL NOT NULL, 
          geo_data_id INT NOT NULL, 
          referent_tag_id INT DEFAULT NULL, 
          countries TEXT NOT NULL, 
          code VARCHAR(6) NOT NULL, 
          number SMALLINT NOT NULL, 
          name VARCHAR(50) NOT NULL, 
          department_code VARCHAR(5) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68E318DC80E32C3E ON districts (geo_data_id)');
        $this->addSql('CREATE UNIQUE INDEX district_code_unique ON districts (code)');
        $this->addSql('CREATE UNIQUE INDEX district_department_code_number ON districts (department_code, number)');
        $this->addSql('CREATE UNIQUE INDEX district_referent_tag_unique ON districts (referent_tag_id)');
        $this->addSql('COMMENT ON COLUMN districts.countries IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE referent_managed_areas (
          id SERIAL NOT NULL, 
          marker_latitude DOUBLE PRECISION DEFAULT NULL, 
          marker_longitude DOUBLE PRECISION DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN referent_managed_areas.marker_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN referent_managed_areas.marker_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE referent_managed_areas_tags (
          referent_managed_area_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            referent_managed_area_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_8BE84DD56B99CC25 ON referent_managed_areas_tags (referent_managed_area_id)');
        $this->addSql('CREATE INDEX IDX_8BE84DD59C262DB3 ON referent_managed_areas_tags (referent_tag_id)');
        $this->addSql('CREATE TABLE referent_team_member (
          id SERIAL NOT NULL, 
          member_id INT NOT NULL, 
          referent_id INT NOT NULL, 
          limited BOOLEAN DEFAULT \'false\' NOT NULL, 
          restricted_cities TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C006717597D3FE ON referent_team_member (member_id)');
        $this->addSql('CREATE INDEX IDX_6C0067135E47E35 ON referent_team_member (referent_id)');
        $this->addSql('COMMENT ON COLUMN referent_team_member.restricted_cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE referent_team_member_committee (
          referent_team_member_id INT NOT NULL, 
          committee_id INT NOT NULL, 
          PRIMARY KEY(
            referent_team_member_id, committee_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_EC89860BFE4CA267 ON referent_team_member_committee (referent_team_member_id)');
        $this->addSql('CREATE INDEX IDX_EC89860BED1A100B ON referent_team_member_committee (committee_id)');
        $this->addSql('CREATE TABLE coordinator_managed_areas (
          id SERIAL NOT NULL, 
          codes TEXT NOT NULL, 
          sector VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN coordinator_managed_areas.codes IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE procuration_managed_areas (
          id SERIAL NOT NULL, 
          codes TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN procuration_managed_areas.codes IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE assessor_managed_areas (
          id SERIAL NOT NULL, 
          codes TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN assessor_managed_areas.codes IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE assessor_role_association (
          id SERIAL NOT NULL, 
          vote_place_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B93395C2F3F90B30 ON assessor_role_association (vote_place_id)');
        $this->addSql('CREATE TABLE municipal_manager_role_association (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE municipal_manager_role_association_cities (
          municipal_manager_role_association_id INT NOT NULL, 
          city_id INT NOT NULL, 
          PRIMARY KEY(
            municipal_manager_role_association_id, 
            city_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_A713D9C2D96891C ON municipal_manager_role_association_cities (
          municipal_manager_role_association_id
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A713D9C28BAC62AF ON municipal_manager_role_association_cities (city_id)');
        $this->addSql('CREATE TABLE municipal_manager_supervisor_role (
          id SERIAL NOT NULL, 
          referent_id INT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F304FF35E47E35 ON municipal_manager_supervisor_role (referent_id)');
        $this->addSql('CREATE TABLE jecoute_managed_areas (
          id SERIAL NOT NULL, 
          zone_id INT DEFAULT NULL, 
          codes TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_DF8531749F2C3FAB ON jecoute_managed_areas (zone_id)');
        $this->addSql('COMMENT ON COLUMN jecoute_managed_areas.codes IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE territorial_council_membership (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          territorial_council_id INT NOT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A99831625F06C53 ON territorial_council_membership (adherent_id)');
        $this->addSql('CREATE INDEX IDX_2A998316AAA61A99 ON territorial_council_membership (territorial_council_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_membership.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE political_committee_membership (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          political_committee_id INT NOT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          is_additional BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD85437B25F06C53 ON political_committee_membership (adherent_id)');
        $this->addSql('CREATE INDEX IDX_FD85437BC7A72 ON political_committee_membership (political_committee_id)');
        $this->addSql('COMMENT ON COLUMN political_committee_membership.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE committees_memberships (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          committee_id INT NOT NULL, 
          privilege VARCHAR(10) NOT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          enable_vote BOOLEAN DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_E7A6490E25F06C53 ON committees_memberships (adherent_id)');
        $this->addSql('CREATE INDEX IDX_E7A6490EED1A100B ON committees_memberships (committee_id)');
        $this->addSql('CREATE INDEX committees_memberships_role_idx ON committees_memberships (privilege)');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_committee ON committees_memberships (adherent_id, committee_id)');
        $this->addSql('CREATE UNIQUE INDEX adherent_votes_in_committee ON committees_memberships (adherent_id, enable_vote)');
        $this->addSql('COMMENT ON COLUMN committees_memberships.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE citizen_project_memberships (
          id SERIAL NOT NULL, 
          citizen_project_id INT NOT NULL, 
          adherent_id INT NOT NULL, 
          privilege VARCHAR(15) NOT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_2E41816B3584533 ON citizen_project_memberships (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_2E4181625F06C53 ON citizen_project_memberships (adherent_id)');
        $this->addSql('CREATE INDEX citizen_project_memberships_role_idx ON citizen_project_memberships (privilege)');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_citizen_project ON citizen_project_memberships (adherent_id, citizen_project_id)');
        $this->addSql('COMMENT ON COLUMN citizen_project_memberships.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE committee_feed_item (
          id SERIAL NOT NULL, 
          committee_id INT DEFAULT NULL, 
          author_id INT DEFAULT NULL, 
          event_id INT DEFAULT NULL, 
          item_type VARCHAR(18) NOT NULL, 
          content TEXT DEFAULT NULL, 
          published BOOLEAN DEFAULT \'true\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_4F1CDC80ED1A100B ON committee_feed_item (committee_id)');
        $this->addSql('CREATE INDEX IDX_4F1CDC80F675F31B ON committee_feed_item (author_id)');
        $this->addSql('CREATE INDEX IDX_4F1CDC8071F7E88B ON committee_feed_item (event_id)');
        $this->addSql('COMMENT ON COLUMN committee_feed_item.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE committee_feed_item_user_documents (
          committee_feed_item_id INT NOT NULL, 
          user_document_id INT NOT NULL, 
          PRIMARY KEY(
            committee_feed_item_id, user_document_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_D269D0AABEF808A3 ON committee_feed_item_user_documents (committee_feed_item_id)');
        $this->addSql('CREATE INDEX IDX_D269D0AA6A24B1A2 ON committee_feed_item_user_documents (user_document_id)');
        $this->addSql('CREATE TABLE adherent_tags (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX adherent_tag_name_unique ON adherent_tags (name)');
        $this->addSql('CREATE TABLE ideas_workshop_idea (
          id SERIAL NOT NULL, 
          category_id INT DEFAULT NULL, 
          author_id INT DEFAULT NULL, 
          committee_id INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          finalized_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          last_contribution_notification_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          enabled BOOLEAN DEFAULT \'true\' NOT NULL, 
          votes_count INT NOT NULL, 
          comments_count INT DEFAULT 0 NOT NULL, 
          author_category VARCHAR(9) NOT NULL, 
          description TEXT DEFAULT NULL, 
          extensions_count SMALLINT NOT NULL, 
          last_extension_date DATE DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          canonical_name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CA001C7212469DE2 ON ideas_workshop_idea (category_id)');
        $this->addSql('CREATE INDEX IDX_CA001C72F675F31B ON ideas_workshop_idea (author_id)');
        $this->addSql('CREATE INDEX IDX_CA001C72ED1A100B ON ideas_workshop_idea (committee_id)');
        $this->addSql('CREATE INDEX idea_workshop_author_category_idx ON ideas_workshop_idea (author_category)');
        $this->addSql('CREATE UNIQUE INDEX idea_uuid_unique ON ideas_workshop_idea (uuid)');
        $this->addSql('CREATE UNIQUE INDEX idea_slug_unique ON ideas_workshop_idea (slug)');
        $this->addSql('COMMENT ON COLUMN ideas_workshop_idea.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE ideas_workshop_ideas_themes (
          idea_id INT NOT NULL, 
          theme_id INT NOT NULL, 
          PRIMARY KEY(idea_id, theme_id)
        )');
        $this->addSql('CREATE INDEX IDX_DB4ED3145B6FEF7D ON ideas_workshop_ideas_themes (idea_id)');
        $this->addSql('CREATE INDEX IDX_DB4ED31459027487 ON ideas_workshop_ideas_themes (theme_id)');
        $this->addSql('CREATE TABLE ideas_workshop_ideas_needs (
          idea_id INT NOT NULL, 
          need_id INT NOT NULL, 
          PRIMARY KEY(idea_id, need_id)
        )');
        $this->addSql('CREATE INDEX IDX_75CEB995B6FEF7D ON ideas_workshop_ideas_needs (idea_id)');
        $this->addSql('CREATE INDEX IDX_75CEB99624AF264 ON ideas_workshop_ideas_needs (need_id)');
        $this->addSql('CREATE TABLE medias (
          id BIGSERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          path VARCHAR(255) NOT NULL, 
          width INT NOT NULL, 
          height INT NOT NULL, 
          size BIGINT NOT NULL, 
          mime_type VARCHAR(50) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          compressed_display BOOLEAN DEFAULT \'true\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12D2AF81B548B0F ON medias (path)');
        $this->addSql('CREATE TABLE municipal_chief_areas (
          id SERIAL NOT NULL, 
          insee_code VARCHAR(255) NOT NULL, 
          jecoute_access BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE senatorial_candidate_areas (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE senatorial_candidate_areas_tags (
          senatorial_candidate_area_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            senatorial_candidate_area_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_F83208FAA7BF84E8 ON senatorial_candidate_areas_tags (senatorial_candidate_area_id)');
        $this->addSql('CREATE INDEX IDX_F83208FA9C262DB3 ON senatorial_candidate_areas_tags (referent_tag_id)');
        $this->addSql('CREATE TABLE lre_area (
          id SERIAL NOT NULL, 
          referent_tag_id INT DEFAULT NULL, 
          all_tags BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_8D3B8F189C262DB3 ON lre_area (referent_tag_id)');
        $this->addSql('CREATE TABLE candidate_managed_area (
          id SERIAL NOT NULL, 
          zone_id INT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_C604D2EA9F2C3FAB ON candidate_managed_area (zone_id)');
        $this->addSql('CREATE TABLE adherent_charter (
          id SMALLSERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          accepted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          dtype VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_D6F94F2B25F06C53 ON adherent_charter (adherent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6F94F2B25F06C5370AAEA5 ON adherent_charter (adherent_id, dtype)');
        $this->addSql('CREATE TABLE senator_area (
          id SERIAL NOT NULL, 
          department_tag_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_D229BBF7AEC89CE1 ON senator_area (department_tag_id)');
        $this->addSql('CREATE TABLE consular_managed_area (
          id SERIAL NOT NULL, 
          consular_district_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_7937A51292CA96FD ON consular_managed_area (consular_district_id)');
        $this->addSql('CREATE TABLE certification_request (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          processed_by_id INT DEFAULT NULL, 
          found_duplicated_adherent_id INT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          status VARCHAR(20) NOT NULL, 
          document_name VARCHAR(255) DEFAULT NULL, 
          document_mime_type VARCHAR(30) DEFAULT NULL, 
          processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          block_reason VARCHAR(30) DEFAULT NULL, 
          custom_block_reason TEXT DEFAULT NULL, 
          block_comment TEXT DEFAULT NULL, 
          refusal_reason VARCHAR(30) DEFAULT NULL, 
          custom_refusal_reason TEXT DEFAULT NULL, 
          refusal_comment TEXT DEFAULT NULL, 
          ocr_payload JSON DEFAULT NULL, 
          ocr_status VARCHAR(255) DEFAULT NULL, 
          ocr_result VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_6E7481A925F06C53 ON certification_request (adherent_id)');
        $this->addSql('CREATE INDEX IDX_6E7481A92FFD4FD3 ON certification_request (processed_by_id)');
        $this->addSql('CREATE INDEX IDX_6E7481A96EA98020 ON certification_request (found_duplicated_adherent_id)');
        $this->addSql('COMMENT ON COLUMN certification_request.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE my_team_delegated_access (
          id SERIAL NOT NULL, 
          delegator_id INT DEFAULT NULL, 
          delegated_id INT DEFAULT NULL, 
          role VARCHAR(255) NOT NULL, 
          accesses TEXT DEFAULT NULL, 
          restricted_cities TEXT DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_421C13B98825BEFA ON my_team_delegated_access (delegator_id)');
        $this->addSql('CREATE INDEX IDX_421C13B9B7E7AE18 ON my_team_delegated_access (delegated_id)');
        $this->addSql('COMMENT ON COLUMN my_team_delegated_access.accesses IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN my_team_delegated_access.restricted_cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN my_team_delegated_access.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE my_team_delegate_access_committee (
          delegated_access_id INT NOT NULL, 
          committee_id INT NOT NULL, 
          PRIMARY KEY(
            delegated_access_id, committee_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_C52A163FFD98FA7A ON my_team_delegate_access_committee (delegated_access_id)');
        $this->addSql('CREATE INDEX IDX_C52A163FED1A100B ON my_team_delegate_access_committee (committee_id)');
        $this->addSql('CREATE TABLE adherent_commitment (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          commitment_actions TEXT DEFAULT NULL, 
          debate_and_propose_ideas_actions TEXT DEFAULT NULL, 
          act_for_territory_actions TEXT DEFAULT NULL, 
          progressivism_actions TEXT DEFAULT NULL, 
          skills TEXT DEFAULT NULL, 
          availability VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D239EF6F25F06C53 ON adherent_commitment (adherent_id)');
        $this->addSql('COMMENT ON COLUMN adherent_commitment.commitment_actions IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherent_commitment.debate_and_propose_ideas_actions IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherent_commitment.act_for_territory_actions IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherent_commitment.progressivism_actions IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherent_commitment.skills IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE thematic_community (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          description TEXT NOT NULL, 
          enabled BOOLEAN NOT NULL, 
          uuid UUID NOT NULL, 
          canonical_name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN thematic_community.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE adherent_mandate (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          committee_id INT DEFAULT NULL, 
          territorial_council_id INT DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          begin_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          finish_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          quality VARCHAR(255) DEFAULT NULL, 
          reason VARCHAR(255) DEFAULT NULL, 
          provisional BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          is_additionally_elected BOOLEAN DEFAULT \'false\', 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_9C0C3D6025F06C53 ON adherent_mandate (adherent_id)');
        $this->addSql('CREATE INDEX IDX_9C0C3D60ED1A100B ON adherent_mandate (committee_id)');
        $this->addSql('CREATE INDEX IDX_9C0C3D60AAA61A99 ON adherent_mandate (territorial_council_id)');
        $this->addSql('COMMENT ON COLUMN adherent_mandate.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE committee_provisional_supervisor (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          committee_id INT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_E394C3D425F06C53 ON committee_provisional_supervisor (adherent_id)');
        $this->addSql('CREATE INDEX IDX_E394C3D4ED1A100B ON committee_provisional_supervisor (committee_id)');
        $this->addSql('CREATE TABLE referent_tags (
          id SERIAL NOT NULL, 
          zone_id INT DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          code VARCHAR(100) NOT NULL, 
          external_id VARCHAR(255) DEFAULT NULL, 
          type VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_135D29D99F2C3FAB ON referent_tags (zone_id)');
        $this->addSql('CREATE UNIQUE INDEX referent_tag_name_unique ON referent_tags (name)');
        $this->addSql('CREATE UNIQUE INDEX referent_tag_code_unique ON referent_tags (code)');
        $this->addSql('CREATE TABLE geo_zone (
          id SERIAL NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          team_code VARCHAR(6) DEFAULT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4CCEF0780E32C3E ON geo_zone (geo_data_id)');
        $this->addSql('CREATE INDEX geo_zone_type_idx ON geo_zone (type)');
        $this->addSql('CREATE UNIQUE INDEX geo_zone_code_type_unique ON geo_zone (code, type)');
        $this->addSql('CREATE TABLE geo_zone_parent (
          child_id INT NOT NULL, 
          parent_id INT NOT NULL, 
          PRIMARY KEY(child_id, parent_id)
        )');
        $this->addSql('CREATE INDEX IDX_8E49B9DDD62C21B ON geo_zone_parent (child_id)');
        $this->addSql('CREATE INDEX IDX_8E49B9D727ACA70 ON geo_zone_parent (parent_id)');
        $this->addSql('CREATE TABLE citizen_project_skills (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX citizen_project_skill_slug_unique ON citizen_project_skills (slug)');
        $this->addSql('CREATE UNIQUE INDEX citizen_project_skill_name_unique ON citizen_project_skills (name)');
        $this->addSql('CREATE TABLE referent (
          id SMALLSERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          email_address VARCHAR(100) DEFAULT NULL, 
          slug VARCHAR(100) NOT NULL, 
          facebook_page_url VARCHAR(255) DEFAULT NULL, 
          twitter_page_url VARCHAR(255) DEFAULT NULL, 
          geojson TEXT DEFAULT NULL, 
          description TEXT DEFAULT NULL, 
          area_label VARCHAR(255) NOT NULL, 
          status VARCHAR(10) DEFAULT \'DISABLED\' NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_FE9AAC6CEA9FDD75 ON referent (media_id)');
        $this->addSql('CREATE UNIQUE INDEX referent_slug_unique ON referent (slug)');
        $this->addSql('CREATE TABLE referent_areas (
          referent_id SMALLINT NOT NULL, 
          area_id SMALLINT NOT NULL, 
          PRIMARY KEY(referent_id, area_id)
        )');
        $this->addSql('CREATE INDEX IDX_75CEBC6C35E47E35 ON referent_areas (referent_id)');
        $this->addSql('CREATE INDEX IDX_75CEBC6CBD0F409C ON referent_areas (area_id)');
        $this->addSql('CREATE TABLE referent_area (
          id SMALLSERIAL NOT NULL, 
          area_code VARCHAR(6) NOT NULL, 
          area_type VARCHAR(20) NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          keywords TEXT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX referent_area_area_code_unique ON referent_area (area_code)');
        $this->addSql('CREATE TABLE referent_person_link (
          id SERIAL NOT NULL, 
          person_organizational_chart_item_id INT DEFAULT NULL, 
          referent_id SMALLINT DEFAULT NULL, 
          adherent_id INT DEFAULT NULL, 
          first_name VARCHAR(255) DEFAULT NULL, 
          last_name VARCHAR(255) DEFAULT NULL, 
          email VARCHAR(255) DEFAULT NULL, 
          phone VARCHAR(255) DEFAULT NULL, 
          postal_address VARCHAR(255) DEFAULT NULL, 
          co_referent VARCHAR(50) DEFAULT NULL, 
          is_jecoute_manager BOOLEAN DEFAULT \'false\' NOT NULL, 
          is_municipal_manager_supervisor BOOLEAN DEFAULT \'false\' NOT NULL, 
          restricted_cities TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_BC75A60A810B5A42 ON referent_person_link (
          person_organizational_chart_item_id
        )');
        $this->addSql('CREATE INDEX IDX_BC75A60A35E47E35 ON referent_person_link (referent_id)');
        $this->addSql('CREATE INDEX IDX_BC75A60A25F06C53 ON referent_person_link (adherent_id)');
        $this->addSql('COMMENT ON COLUMN referent_person_link.restricted_cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE referent_person_link_committee (
          referent_person_link_id INT NOT NULL, 
          committee_id INT NOT NULL, 
          PRIMARY KEY(
            referent_person_link_id, committee_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_1C97B2A5B3E4DE86 ON referent_person_link_committee (referent_person_link_id)');
        $this->addSql('CREATE INDEX IDX_1C97B2A5ED1A100B ON referent_person_link_committee (committee_id)');
        $this->addSql('CREATE TABLE user_list_definition (
          id SERIAL NOT NULL, 
          type VARCHAR(50) NOT NULL, 
          code VARCHAR(100) NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          color VARCHAR(7) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX user_list_definition_type_code_unique ON user_list_definition (type, code)');
        $this->addSql('CREATE TABLE je_marche_reports (
          id SERIAL NOT NULL, 
          type VARCHAR(30) NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          postal_code VARCHAR(11) NOT NULL, 
          convinced TEXT DEFAULT NULL, 
          almost_convinced TEXT DEFAULT NULL, 
          not_convinced SMALLINT DEFAULT NULL, 
          reaction TEXT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN je_marche_reports.convinced IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN je_marche_reports.almost_convinced IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE invitations (
          id SERIAL NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          email VARCHAR(255) NOT NULL, 
          message TEXT NOT NULL, 
          client_ip VARCHAR(50) DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN invitations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE citizen_project_category_skills (
          id SERIAL NOT NULL, 
          category_id INT DEFAULT NULL, 
          skill_id INT DEFAULT NULL, 
          promotion BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_168C868A12469DE2 ON citizen_project_category_skills (category_id)');
        $this->addSql('CREATE INDEX IDX_168C868A5585C142 ON citizen_project_category_skills (skill_id)');
        $this->addSql('CREATE TABLE referent_managed_users_message (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          include_adherents_no_committee BOOLEAN DEFAULT \'false\' NOT NULL, 
          include_adherents_in_committee BOOLEAN DEFAULT \'false\' NOT NULL, 
          include_hosts BOOLEAN DEFAULT \'false\' NOT NULL, 
          include_supervisors BOOLEAN DEFAULT \'false\' NOT NULL, 
          query_zone VARCHAR(255) DEFAULT NULL, 
          query_area_code TEXT NOT NULL, 
          query_id TEXT NOT NULL, 
          interests TEXT DEFAULT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          first_name VARCHAR(50) DEFAULT NULL, 
          last_name VARCHAR(50) DEFAULT NULL, 
          age_minimum INT DEFAULT NULL, 
          age_maximum INT DEFAULT NULL, 
          include_cp BOOLEAN DEFAULT \'false\' NOT NULL, 
          registered_from DATE DEFAULT NULL, 
          registered_to DATE DEFAULT NULL, 
          subject VARCHAR(255) NOT NULL, 
          content TEXT NOT NULL, 
          "offset" BIGINT NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_1E41AC6125F06C53 ON referent_managed_users_message (adherent_id)');
        $this->addSql('COMMENT ON COLUMN referent_managed_users_message.interests IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN referent_managed_users_message.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE ton_macron_choices (
          id SERIAL NOT NULL, 
          step SMALLINT NOT NULL, 
          content_key VARCHAR(30) NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          content TEXT NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX ton_macron_choices_uuid_unique ON ton_macron_choices (uuid)');
        $this->addSql('CREATE UNIQUE INDEX ton_macron_choices_content_key_unique ON ton_macron_choices (content_key)');
        $this->addSql('COMMENT ON COLUMN ton_macron_choices.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE summaries (
          id SERIAL NOT NULL, 
          member_id INT DEFAULT NULL, 
          slug VARCHAR(255) NOT NULL, 
          current_profession VARCHAR(255) DEFAULT NULL, 
          contribution_wish VARCHAR(255) NOT NULL, 
          availabilities TEXT DEFAULT NULL, 
          job_locations TEXT DEFAULT NULL, 
          professional_synopsis TEXT NOT NULL, 
          motivation TEXT NOT NULL, 
          showing_recent_activities BOOLEAN NOT NULL, 
          contact_email VARCHAR(255) NOT NULL, 
          linked_in_url VARCHAR(255) DEFAULT NULL, 
          website_url VARCHAR(255) DEFAULT NULL, 
          facebook_url VARCHAR(255) DEFAULT NULL, 
          twitter_nickname VARCHAR(255) DEFAULT NULL, 
          viadeo_url VARCHAR(255) DEFAULT NULL, 
          public BOOLEAN DEFAULT \'false\' NOT NULL, 
          picture_uploaded BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_66783CCA7597D3FE ON summaries (member_id)');
        $this->addSql('COMMENT ON COLUMN summaries.availabilities IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN summaries.job_locations IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE summary_mission_type_wishes (
          summary_id INT NOT NULL, 
          mission_type_id INT NOT NULL, 
          PRIMARY KEY(summary_id, mission_type_id)
        )');
        $this->addSql('CREATE INDEX IDX_7F3FC70F2AC2D45C ON summary_mission_type_wishes (summary_id)');
        $this->addSql('CREATE INDEX IDX_7F3FC70F547018DE ON summary_mission_type_wishes (mission_type_id)');
        $this->addSql('CREATE TABLE summary_skills (
          summary_id INT NOT NULL, 
          skill_id INT NOT NULL, 
          PRIMARY KEY(summary_id, skill_id)
        )');
        $this->addSql('CREATE INDEX IDX_2FD2B63C2AC2D45C ON summary_skills (summary_id)');
        $this->addSql('CREATE INDEX IDX_2FD2B63C5585C142 ON summary_skills (skill_id)');
        $this->addSql('CREATE TABLE live_links (
          id SERIAL NOT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          link VARCHAR(255) NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE facebook_profiles (
          id SERIAL NOT NULL, 
          facebook_id VARCHAR(30) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          email_address VARCHAR(255) DEFAULT NULL, 
          gender VARCHAR(30) NOT NULL, 
          age_range JSON NOT NULL, 
          access_token VARCHAR(255) DEFAULT NULL, 
          has_auto_uploaded BOOLEAN NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX facebook_profile_uuid ON facebook_profiles (uuid)');
        $this->addSql('CREATE UNIQUE INDEX facebook_profile_facebook_id ON facebook_profiles (facebook_id)');
        $this->addSql('COMMENT ON COLUMN facebook_profiles.age_range IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN facebook_profiles.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE organizational_chart_item (
          id SERIAL NOT NULL, 
          tree_root INT DEFAULT NULL, 
          parent_id INT DEFAULT NULL, 
          label VARCHAR(255) NOT NULL, 
          lft INT NOT NULL, 
          lvl INT NOT NULL, 
          rgt INT NOT NULL, 
          type VARCHAR(20) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_29C1CBACA977936C ON organizational_chart_item (tree_root)');
        $this->addSql('CREATE INDEX IDX_29C1CBAC727ACA70 ON organizational_chart_item (parent_id)');
        $this->addSql('CREATE TABLE events_invitations (
          id SERIAL NOT NULL, 
          event_id INT DEFAULT NULL, 
          email VARCHAR(255) NOT NULL, 
          message TEXT NOT NULL, 
          guests TEXT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_B94D5AAD71F7E88B ON events_invitations (event_id)');
        $this->addSql('COMMENT ON COLUMN events_invitations.guests IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN events_invitations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE thematic_community_membership (
          id SERIAL NOT NULL, 
          community_id INT DEFAULT NULL, 
          adherent_id INT DEFAULT NULL, 
          contact_id INT DEFAULT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          has_job BOOLEAN DEFAULT \'false\' NOT NULL, 
          job VARCHAR(255) DEFAULT NULL, 
          association BOOLEAN DEFAULT \'false\' NOT NULL, 
          association_name VARCHAR(255) DEFAULT NULL, 
          motivations TEXT DEFAULT NULL, 
          expert BOOLEAN DEFAULT \'false\' NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          uuid UUID NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_22B6AC05FDA7B0BF ON thematic_community_membership (community_id)');
        $this->addSql('CREATE INDEX IDX_22B6AC0525F06C53 ON thematic_community_membership (adherent_id)');
        $this->addSql('CREATE INDEX IDX_22B6AC05E7A1254A ON thematic_community_membership (contact_id)');
        $this->addSql('COMMENT ON COLUMN thematic_community_membership.motivations IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN thematic_community_membership.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE thematic_community_membership_user_list_definition (
          thematic_community_membership_id INT NOT NULL, 
          user_list_definition_id INT NOT NULL, 
          PRIMARY KEY(
            thematic_community_membership_id, 
            user_list_definition_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_58815EB9403AE2A5 ON thematic_community_membership_user_list_definition (
          thematic_community_membership_id
        )');
        $this->addSql('CREATE INDEX IDX_58815EB9F74563E3 ON thematic_community_membership_user_list_definition (user_list_definition_id)');
        $this->addSql('CREATE TABLE thematic_community_contact (
          id SERIAL NOT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          email VARCHAR(255) NOT NULL, 
          gender VARCHAR(255) DEFAULT NULL, 
          custom_gender VARCHAR(255) DEFAULT NULL, 
          birth_date DATE DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          activity_area VARCHAR(255) DEFAULT NULL, 
          job_area VARCHAR(255) DEFAULT NULL, 
          job VARCHAR(255) DEFAULT NULL, 
          position VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN thematic_community_contact.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN thematic_community_contact.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN thematic_community_contact.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN thematic_community_contact.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE events_registrations (
          id SERIAL NOT NULL, 
          event_id INT DEFAULT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          newsletter_subscriber BOOLEAN NOT NULL, 
          adherent_uuid UUID DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_EEFA30C071F7E88B ON events_registrations (event_id)');
        $this->addSql('CREATE INDEX event_registration_email_address_idx ON events_registrations (email_address)');
        $this->addSql('CREATE INDEX event_registration_adherent_uuid_idx ON events_registrations (adherent_uuid)');
        $this->addSql('COMMENT ON COLUMN events_registrations.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN events_registrations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE region (
          id SERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          code VARCHAR(10) NOT NULL, 
          country VARCHAR(2) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F62F17677153098 ON region (code)');
        $this->addSql('CREATE TABLE projection_managed_users (
          id BIGSERIAL NOT NULL, 
          status SMALLINT NOT NULL, 
          type VARCHAR(20) NOT NULL, 
          original_id BIGINT NOT NULL, 
          adherent_uuid UUID DEFAULT NULL, 
          email VARCHAR(255) NOT NULL, 
          address VARCHAR(150) DEFAULT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          committee_postal_code VARCHAR(15) DEFAULT NULL, 
          city VARCHAR(255) DEFAULT NULL, 
          country VARCHAR(2) DEFAULT NULL, 
          first_name VARCHAR(50) DEFAULT NULL, 
          last_name VARCHAR(50) DEFAULT NULL, 
          age SMALLINT DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          committees TEXT DEFAULT NULL, 
          committee_uuids TEXT DEFAULT NULL, 
          is_committee_member BOOLEAN NOT NULL, 
          is_committee_host BOOLEAN NOT NULL, 
          is_committee_supervisor BOOLEAN NOT NULL, 
          is_committee_provisional_supervisor BOOLEAN NOT NULL, 
          subscribed_tags TEXT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          interests TEXT DEFAULT NULL, 
          supervisor_tags TEXT DEFAULT NULL, 
          citizen_projects JSON DEFAULT NULL, 
          citizen_projects_organizer JSON DEFAULT NULL, 
          subscription_types TEXT DEFAULT NULL, 
          vote_committee_id INT DEFAULT NULL, 
          certified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX projection_managed_users_search ON projection_managed_users (status, postal_code, country)');
        $this->addSql('COMMENT ON COLUMN projection_managed_users.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN projection_managed_users.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN projection_managed_users.committee_uuids IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN projection_managed_users.interests IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN projection_managed_users.supervisor_tags IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN projection_managed_users.subscription_types IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE projection_managed_users_zone (
          managed_user_id BIGINT NOT NULL, 
          zone_id INT NOT NULL, 
          PRIMARY KEY(managed_user_id, zone_id)
        )');
        $this->addSql('CREATE INDEX IDX_E4D4ADCDC679DD78 ON projection_managed_users_zone (managed_user_id)');
        $this->addSql('CREATE INDEX IDX_E4D4ADCD9F2C3FAB ON projection_managed_users_zone (zone_id)');
        $this->addSql('CREATE TABLE administrators (
          id SERIAL NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          password VARCHAR(255) NOT NULL, 
          google_authenticator_secret VARCHAR(255) DEFAULT NULL, 
          roles TEXT NOT NULL, 
          activated BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX administrators_email_address_unique ON administrators (email_address)');
        $this->addSql('COMMENT ON COLUMN administrators.roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE adherent_activation_keys (
          id SERIAL NOT NULL, 
          adherent_uuid UUID NOT NULL, 
          value VARCHAR(40) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX adherent_activation_token_unique ON adherent_activation_keys (value)');
        $this->addSql('CREATE UNIQUE INDEX adherent_activation_token_account_unique ON adherent_activation_keys (value, adherent_uuid)');
        $this->addSql('COMMENT ON COLUMN adherent_activation_keys.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN adherent_activation_keys.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE redirections (
          id SERIAL NOT NULL, 
          url_from VARCHAR(255) NOT NULL, 
          url_to VARCHAR(255) NOT NULL, 
          type INT NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE jecoute_survey (
          id SERIAL NOT NULL, 
          author_id INT DEFAULT NULL, 
          zone_id INT DEFAULT NULL, 
          administrator_id INT DEFAULT NULL, 
          name VARCHAR(255) NOT NULL, 
          published BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          city VARCHAR(255) DEFAULT NULL, 
          tags TEXT DEFAULT NULL, 
          blocked_changes BOOLEAN DEFAULT \'false\', 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_EC4948E5F675F31B ON jecoute_survey (author_id)');
        $this->addSql('CREATE INDEX IDX_EC4948E59F2C3FAB ON jecoute_survey (zone_id)');
        $this->addSql('CREATE INDEX IDX_EC4948E54B09E92C ON jecoute_survey (administrator_id)');
        $this->addSql('COMMENT ON COLUMN jecoute_survey.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN jecoute_survey.tags IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE jecoute_region (
          id SERIAL NOT NULL, 
          geo_region_id INT NOT NULL, 
          uuid UUID NOT NULL, 
          subtitle VARCHAR(255) NOT NULL, 
          description TEXT NOT NULL, 
          primary_color VARCHAR(255) NOT NULL, 
          external_link VARCHAR(255) DEFAULT NULL, 
          banner VARCHAR(255) DEFAULT NULL, 
          logo VARCHAR(255) DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E74226F39192B5C ON jecoute_region (geo_region_id)');
        $this->addSql('COMMENT ON COLUMN jecoute_region.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE jecoute_question (
          id SERIAL NOT NULL, 
          content VARCHAR(255) NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          discr VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE jecoute_choice (
          id SERIAL NOT NULL, 
          question_id INT DEFAULT NULL, 
          content VARCHAR(255) NOT NULL, 
          position SMALLINT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_80BD898B1E27F6BF ON jecoute_choice (question_id)');
        $this->addSql('CREATE TABLE jecoute_suggested_question (
          id INT NOT NULL, 
          published BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE jecoute_news (
          id SERIAL NOT NULL, 
          zone_id INT DEFAULT NULL, 
          created_by_id INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          text TEXT NOT NULL, 
          external_link VARCHAR(255) DEFAULT NULL, 
          topic VARCHAR(255) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_34362099F2C3FAB ON jecoute_news (zone_id)');
        $this->addSql('CREATE INDEX IDX_3436209B03A8386 ON jecoute_news (created_by_id)');
        $this->addSql('COMMENT ON COLUMN jecoute_news.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE jecoute_data_answer (
          id SERIAL NOT NULL, 
          survey_question_id INT DEFAULT NULL, 
          data_survey_id INT DEFAULT NULL, 
          text_field VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_12FB393EA6DF29BA ON jecoute_data_answer (survey_question_id)');
        $this->addSql('CREATE INDEX IDX_12FB393E3C5110AB ON jecoute_data_answer (data_survey_id)');
        $this->addSql('CREATE TABLE jecoute_data_answer_selected_choices (
          data_answer_id INT NOT NULL, 
          choice_id INT NOT NULL, 
          PRIMARY KEY(data_answer_id, choice_id)
        )');
        $this->addSql('CREATE INDEX IDX_10DF117259C0831 ON jecoute_data_answer_selected_choices (data_answer_id)');
        $this->addSql('CREATE INDEX IDX_10DF117998666D1 ON jecoute_data_answer_selected_choices (choice_id)');
        $this->addSql('CREATE TABLE jecoute_survey_question (
          id SERIAL NOT NULL, 
          survey_id INT DEFAULT NULL, 
          question_id INT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          from_suggested_question INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A2FBFA81B3FE509D ON jecoute_survey_question (survey_id)');
        $this->addSql('CREATE INDEX IDX_A2FBFA811E27F6BF ON jecoute_survey_question (question_id)');
        $this->addSql('COMMENT ON COLUMN jecoute_survey_question.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE jecoute_data_survey (
          id SERIAL NOT NULL, 
          author_id INT DEFAULT NULL, 
          device_id INT DEFAULT NULL, 
          survey_id INT NOT NULL, 
          posted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          first_name VARCHAR(50) DEFAULT NULL, 
          last_name VARCHAR(50) DEFAULT NULL, 
          email_address VARCHAR(255) DEFAULT NULL, 
          agreed_to_stay_in_contact BOOLEAN NOT NULL, 
          agreed_to_contact_for_join BOOLEAN NOT NULL, 
          agreed_to_treat_personal_data BOOLEAN NOT NULL, 
          postal_code VARCHAR(5) DEFAULT NULL, 
          profession VARCHAR(30) DEFAULT NULL, 
          age_range VARCHAR(15) DEFAULT NULL, 
          gender VARCHAR(15) DEFAULT NULL, 
          gender_other VARCHAR(50) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_6579E8E7F675F31B ON jecoute_data_survey (author_id)');
        $this->addSql('CREATE INDEX IDX_6579E8E794A4C7D4 ON jecoute_data_survey (device_id)');
        $this->addSql('CREATE INDEX IDX_6579E8E7B3FE509D ON jecoute_data_survey (survey_id)');
        $this->addSql('CREATE TABLE election_rounds (
          id SERIAL NOT NULL, 
          election_id INT NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          description VARCHAR(255) DEFAULT NULL, 
          date DATE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_37C02EA0A708DAFF ON election_rounds (election_id)');
        $this->addSql('CREATE TABLE articles_categories (
          id SERIAL NOT NULL, 
          position SMALLINT NOT NULL, 
          name VARCHAR(50) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          cta_link VARCHAR(255) DEFAULT NULL, 
          cta_label VARCHAR(100) DEFAULT NULL, 
          display BOOLEAN DEFAULT \'true\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DE004A0E989D9B62 ON articles_categories (slug)');
        $this->addSql('CREATE TABLE social_shares (
          id BIGSERIAL NOT NULL, 
          social_share_category_id BIGINT DEFAULT NULL, 
          media_id BIGINT DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          type VARCHAR(10) NOT NULL, 
          description TEXT NOT NULL, 
          default_url VARCHAR(255) NOT NULL, 
          facebook_url VARCHAR(255) DEFAULT NULL, 
          twitter_url VARCHAR(255) DEFAULT NULL, 
          published BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          position SMALLINT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_8E1413A085040FAD ON social_shares (social_share_category_id)');
        $this->addSql('CREATE INDEX IDX_8E1413A0EA9FDD75 ON social_shares (media_id)');
        $this->addSql('CREATE TABLE formation_paths (
          id SERIAL NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          description TEXT NOT NULL, 
          position SMALLINT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD311864989D9B62 ON formation_paths (slug)');
        $this->addSql('CREATE TABLE formation_files (
          id SERIAL NOT NULL, 
          module_id BIGINT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          path VARCHAR(255) NOT NULL, 
          extension VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_70BEDE2CAFC2B591 ON formation_files (module_id)');
        $this->addSql('CREATE UNIQUE INDEX formation_file_slug_extension ON formation_files (slug, extension)');
        $this->addSql('CREATE TABLE formation_modules (
          id BIGSERIAL NOT NULL, 
          axe_id BIGINT DEFAULT NULL, 
          media_id BIGINT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          description TEXT NOT NULL, 
          content TEXT NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          position SMALLINT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6B4806AC2B36786B ON formation_modules (title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6B4806AC989D9B62 ON formation_modules (slug)');
        $this->addSql('CREATE INDEX IDX_6B4806AC2E30CD41 ON formation_modules (axe_id)');
        $this->addSql('CREATE INDEX IDX_6B4806ACEA9FDD75 ON formation_modules (media_id)');
        $this->addSql('CREATE TABLE formation_axes (
          id BIGSERIAL NOT NULL, 
          path_id INT NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          description TEXT NOT NULL, 
          content TEXT NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          position SMALLINT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E652CB6989D9B62 ON formation_axes (slug)');
        $this->addSql('CREATE INDEX IDX_7E652CB6D96C566B ON formation_axes (path_id)');
        $this->addSql('CREATE INDEX IDX_7E652CB6EA9FDD75 ON formation_axes (media_id)');
        $this->addSql('CREATE TABLE citizen_projects (
          id SERIAL NOT NULL, 
          category_id INT DEFAULT NULL, 
          turnkey_project_id INT DEFAULT NULL, 
          slug VARCHAR(255) NOT NULL, 
          subtitle VARCHAR(255) NOT NULL, 
          problem_description TEXT DEFAULT NULL, 
          proposed_solution TEXT DEFAULT NULL, 
          required_means TEXT DEFAULT NULL, 
          matched_skills BOOLEAN DEFAULT \'false\' NOT NULL, 
          featured BOOLEAN DEFAULT \'false\' NOT NULL, 
          admin_comment TEXT DEFAULT NULL, 
          district VARCHAR(50) DEFAULT NULL, 
          image_uploaded BOOLEAN DEFAULT \'false\' NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          status VARCHAR(20) NOT NULL, 
          approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          created_by UUID DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          members_count SMALLINT NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          canonical_name VARCHAR(255) NOT NULL, 
          mailchimp_id INT DEFAULT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_651490212469DE2 ON citizen_projects (category_id)');
        $this->addSql('CREATE INDEX IDX_6514902B5315DF4 ON citizen_projects (turnkey_project_id)');
        $this->addSql('CREATE INDEX citizen_project_status_idx ON citizen_projects (status)');
        $this->addSql('CREATE UNIQUE INDEX citizen_project_uuid_unique ON citizen_projects (uuid)');
        $this->addSql('CREATE UNIQUE INDEX citizen_project_slug_unique ON citizen_projects (slug)');
        $this->addSql('COMMENT ON COLUMN citizen_projects.created_by IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN citizen_projects.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN citizen_projects.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN citizen_projects.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN citizen_projects.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE citizen_projects_skills (
          citizen_project_id INT NOT NULL, 
          citizen_project_skill_id INT NOT NULL, 
          PRIMARY KEY(
            citizen_project_id, citizen_project_skill_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_B3D202D9B3584533 ON citizen_projects_skills (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_B3D202D9EA64A9D0 ON citizen_projects_skills (citizen_project_skill_id)');
        $this->addSql('CREATE TABLE citizen_project_referent_tag (
          citizen_project_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            citizen_project_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_73ED204AB3584533 ON citizen_project_referent_tag (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_73ED204A9C262DB3 ON citizen_project_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE legislative_district_zones (
          id SMALLSERIAL NOT NULL, 
          area_code VARCHAR(4) NOT NULL, 
          area_type VARCHAR(20) NOT NULL, 
          rank SMALLINT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          keywords TEXT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX legislative_district_zones_area_code_unique ON legislative_district_zones (area_code)');
        $this->addSql('CREATE TABLE geo_region (
          id SERIAL NOT NULL, 
          country_id INT NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4B3C80877153098 ON geo_region (code)');
        $this->addSql('CREATE INDEX IDX_A4B3C808F92F3E70 ON geo_region (country_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4B3C80880E32C3E ON geo_region (geo_data_id)');
        $this->addSql('CREATE TABLE geo_consular_district (
          id SERIAL NOT NULL, 
          foreign_district_id INT DEFAULT NULL, 
          geo_data_id INT DEFAULT NULL, 
          cities TEXT NOT NULL, 
          number SMALLINT NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBFC552F77153098 ON geo_consular_district (code)');
        $this->addSql('CREATE INDEX IDX_BBFC552F72D24D35 ON geo_consular_district (foreign_district_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBFC552F80E32C3E ON geo_consular_district (geo_data_id)');
        $this->addSql('COMMENT ON COLUMN geo_consular_district.cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE geo_canton (
          id SERIAL NOT NULL, 
          department_id INT NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F04FC05F77153098 ON geo_canton (code)');
        $this->addSql('CREATE INDEX IDX_F04FC05FAE80F5DF ON geo_canton (department_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F04FC05F80E32C3E ON geo_canton (geo_data_id)');
        $this->addSql('CREATE TABLE geo_district (
          id SERIAL NOT NULL, 
          department_id INT NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          number SMALLINT NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DF78232677153098 ON geo_district (code)');
        $this->addSql('CREATE INDEX IDX_DF782326AE80F5DF ON geo_district (department_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DF78232680E32C3E ON geo_district (geo_data_id)');
        $this->addSql('CREATE TABLE geo_borough (
          id SERIAL NOT NULL, 
          city_id INT NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          postal_code TEXT DEFAULT NULL, 
          population INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1449587477153098 ON geo_borough (code)');
        $this->addSql('CREATE INDEX IDX_144958748BAC62AF ON geo_borough (city_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1449587480E32C3E ON geo_borough (geo_data_id)');
        $this->addSql('COMMENT ON COLUMN geo_borough.postal_code IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE geo_country (
          id SERIAL NOT NULL, 
          foreign_district_id INT DEFAULT NULL, 
          geo_data_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E465446477153098 ON geo_country (code)');
        $this->addSql('CREATE INDEX IDX_E465446472D24D35 ON geo_country (foreign_district_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E465446480E32C3E ON geo_country (geo_data_id)');
        $this->addSql('CREATE TABLE geo_custom_zone (
          id SERIAL NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ABE4DB5A77153098 ON geo_custom_zone (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ABE4DB5A80E32C3E ON geo_custom_zone (geo_data_id)');
        $this->addSql('CREATE TABLE geo_foreign_district (
          id SERIAL NOT NULL, 
          custom_zone_id INT NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          number SMALLINT NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_973BE1F177153098 ON geo_foreign_district (code)');
        $this->addSql('CREATE INDEX IDX_973BE1F198755666 ON geo_foreign_district (custom_zone_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_973BE1F180E32C3E ON geo_foreign_district (geo_data_id)');
        $this->addSql('CREATE TABLE geo_city (
          id SERIAL NOT NULL, 
          department_id INT DEFAULT NULL, 
          city_community_id INT DEFAULT NULL, 
          replacement_id INT DEFAULT NULL, 
          geo_data_id INT DEFAULT NULL, 
          postal_code TEXT DEFAULT NULL, 
          population INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_297C2D3477153098 ON geo_city (code)');
        $this->addSql('CREATE INDEX IDX_297C2D34AE80F5DF ON geo_city (department_id)');
        $this->addSql('CREATE INDEX IDX_297C2D346D3B1930 ON geo_city (city_community_id)');
        $this->addSql('CREATE INDEX IDX_297C2D349D25CF90 ON geo_city (replacement_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_297C2D3480E32C3E ON geo_city (geo_data_id)');
        $this->addSql('COMMENT ON COLUMN geo_city.postal_code IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE geo_city_district (
          city_id INT NOT NULL, 
          district_id INT NOT NULL, 
          PRIMARY KEY(city_id, district_id)
        )');
        $this->addSql('CREATE INDEX IDX_5C4191F8BAC62AF ON geo_city_district (city_id)');
        $this->addSql('CREATE INDEX IDX_5C4191FB08FA272 ON geo_city_district (district_id)');
        $this->addSql('CREATE TABLE geo_city_canton (
          city_id INT NOT NULL, 
          canton_id INT NOT NULL, 
          PRIMARY KEY(city_id, canton_id)
        )');
        $this->addSql('CREATE INDEX IDX_A4AB64718BAC62AF ON geo_city_canton (city_id)');
        $this->addSql('CREATE INDEX IDX_A4AB64718D070D0B ON geo_city_canton (canton_id)');
        $this->addSql('CREATE TABLE geo_department (
          id SERIAL NOT NULL, 
          region_id INT NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B460660477153098 ON geo_department (code)');
        $this->addSql('CREATE INDEX IDX_B460660498260155 ON geo_department (region_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B460660480E32C3E ON geo_department (geo_data_id)');
        $this->addSql('CREATE TABLE geo_city_community (
          id SERIAL NOT NULL, 
          geo_data_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          active BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E5805E0877153098 ON geo_city_community (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E5805E0880E32C3E ON geo_city_community (geo_data_id)');
        $this->addSql('CREATE TABLE geo_city_community_department (
          city_community_id INT NOT NULL, 
          department_id INT NOT NULL, 
          PRIMARY KEY(
            city_community_id, department_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_1E2D6D066D3B1930 ON geo_city_community_department (city_community_id)');
        $this->addSql('CREATE INDEX IDX_1E2D6D06AE80F5DF ON geo_city_community_department (department_id)');
        $this->addSql('CREATE TABLE mooc_elements (
          id SERIAL NOT NULL, 
          chapter_id INT DEFAULT NULL, 
          image_id INT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          position SMALLINT NOT NULL, 
          content TEXT DEFAULT NULL, 
          share_twitter_text VARCHAR(255) NOT NULL, 
          share_facebook_text VARCHAR(255) NOT NULL, 
          share_email_subject VARCHAR(255) NOT NULL, 
          share_email_body VARCHAR(500) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          youtube_id VARCHAR(255) DEFAULT NULL, 
          duration TIME(0) WITHOUT TIME ZONE DEFAULT NULL, 
          typeform_url VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_691284C5579F4768 ON mooc_elements (chapter_id)');
        $this->addSql('CREATE INDEX IDX_691284C53DA5256D ON mooc_elements (image_id)');
        $this->addSql('CREATE UNIQUE INDEX mooc_element_slug ON mooc_elements (slug, chapter_id)');
        $this->addSql('CREATE TABLE mooc_element_attachment_link (
          base_mooc_element_id INT NOT NULL, 
          attachment_link_id INT NOT NULL, 
          PRIMARY KEY(
            base_mooc_element_id, attachment_link_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_324635C7B1828C9D ON mooc_element_attachment_link (base_mooc_element_id)');
        $this->addSql('CREATE INDEX IDX_324635C7653157F7 ON mooc_element_attachment_link (attachment_link_id)');
        $this->addSql('CREATE TABLE mooc_element_attachment_file (
          base_mooc_element_id INT NOT NULL, 
          attachment_file_id INT NOT NULL, 
          PRIMARY KEY(
            base_mooc_element_id, attachment_file_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_88759A26B1828C9D ON mooc_element_attachment_file (base_mooc_element_id)');
        $this->addSql('CREATE INDEX IDX_88759A265B5E2CEA ON mooc_element_attachment_file (attachment_file_id)');
        $this->addSql('CREATE TABLE mooc (
          id SERIAL NOT NULL, 
          article_image_id INT DEFAULT NULL, 
          list_image_id INT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          description TEXT DEFAULT NULL, 
          slug VARCHAR(255) NOT NULL, 
          content VARCHAR(800) DEFAULT NULL, 
          youtube_id VARCHAR(255) DEFAULT NULL, 
          youtube_duration TIME(0) WITHOUT TIME ZONE DEFAULT NULL, 
          share_twitter_text VARCHAR(255) NOT NULL, 
          share_facebook_text VARCHAR(255) NOT NULL, 
          share_email_subject VARCHAR(255) NOT NULL, 
          share_email_body VARCHAR(500) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D5D3B55684DD106 ON mooc (article_image_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D5D3B5543C8160D ON mooc (list_image_id)');
        $this->addSql('CREATE UNIQUE INDEX mooc_slug ON mooc (slug)');
        $this->addSql('CREATE TABLE mooc_attachment_link (
          id SERIAL NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          link VARCHAR(255) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE mooc_chapter (
          id SERIAL NOT NULL, 
          mooc_id INT DEFAULT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          published BOOLEAN DEFAULT \'false\' NOT NULL, 
          published_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          position SMALLINT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A3EDA0D1255EEB87 ON mooc_chapter (mooc_id)');
        $this->addSql('CREATE UNIQUE INDEX mooc_chapter_slug ON mooc_chapter (slug)');
        $this->addSql('CREATE TABLE mooc_attachment_file (
          id SERIAL NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          path VARCHAR(255) NOT NULL, 
          extension VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX mooc_attachment_file_slug_extension ON mooc_attachment_file (slug, extension)');
        $this->addSql('CREATE TABLE order_sections (
          id SERIAL NOT NULL, 
          name VARCHAR(50) NOT NULL, 
          position SMALLINT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE deputy_managed_users_message (
          id SERIAL NOT NULL, 
          district_id INT DEFAULT NULL, 
          adherent_id INT DEFAULT NULL, 
          subject VARCHAR(255) NOT NULL, 
          content TEXT NOT NULL, 
          "offset" BIGINT NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_5AC419DDB08FA272 ON deputy_managed_users_message (district_id)');
        $this->addSql('CREATE INDEX IDX_5AC419DD25F06C53 ON deputy_managed_users_message (adherent_id)');
        $this->addSql('COMMENT ON COLUMN deputy_managed_users_message.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE assessor_requests (
          id SERIAL NOT NULL, 
          vote_place_id INT DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          first_name VARCHAR(100) NOT NULL, 
          birth_name VARCHAR(50) DEFAULT NULL, 
          birthdate DATE NOT NULL, 
          birth_city VARCHAR(50) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          city VARCHAR(50) NOT NULL, 
          vote_city VARCHAR(50) NOT NULL, 
          office_number VARCHAR(10) NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          phone VARCHAR(35) NOT NULL, 
          assessor_city VARCHAR(50) DEFAULT NULL, 
          assessor_postal_code VARCHAR(15) DEFAULT NULL, 
          assessor_country VARCHAR(2) NOT NULL, 
          office VARCHAR(15) NOT NULL, 
          processed BOOLEAN NOT NULL, 
          processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          enabled BOOLEAN NOT NULL, 
          reachable BOOLEAN DEFAULT \'false\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_26BC800F3F90B30 ON assessor_requests (vote_place_id)');
        $this->addSql('COMMENT ON COLUMN assessor_requests.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN assessor_requests.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE assessor_requests_vote_place_wishes (
          assessor_request_id INT NOT NULL, 
          vote_place_id INT NOT NULL, 
          PRIMARY KEY(
            assessor_request_id, vote_place_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_1517FC131BD1903D ON assessor_requests_vote_place_wishes (assessor_request_id)');
        $this->addSql('CREATE INDEX IDX_1517FC13F3F90B30 ON assessor_requests_vote_place_wishes (vote_place_id)');
        $this->addSql('CREATE TABLE elections (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          introduction TEXT NOT NULL, 
          proposal_content TEXT DEFAULT NULL, 
          request_content TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BD26F335E237E06 ON elections (name)');
        $this->addSql('CREATE TABLE banned_adherent (
          id SERIAL NOT NULL, 
          date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN banned_adherent.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE ideas_workshop_idea_notification_dates (
          id SERIAL NOT NULL, 
          last_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          caution_last_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE ideas_workshop_thread (
          id SERIAL NOT NULL, 
          answer_id INT NOT NULL, 
          author_id INT NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          content TEXT NOT NULL, 
          approved BOOLEAN NOT NULL, 
          enabled BOOLEAN DEFAULT \'true\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CE975BDDAA334807 ON ideas_workshop_thread (answer_id)');
        $this->addSql('CREATE INDEX IDX_CE975BDDF675F31B ON ideas_workshop_thread (author_id)');
        $this->addSql('CREATE UNIQUE INDEX threads_uuid_unique ON ideas_workshop_thread (uuid)');
        $this->addSql('COMMENT ON COLUMN ideas_workshop_thread.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE ideas_workshop_question (
          id SERIAL NOT NULL, 
          guideline_id INT NOT NULL, 
          placeholder VARCHAR(255) NOT NULL, 
          position SMALLINT NOT NULL, 
          category VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          required BOOLEAN NOT NULL, 
          enabled BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_111C43E4CC0B46A8 ON ideas_workshop_question (guideline_id)');
        $this->addSql('CREATE TABLE ideas_workshop_comment (
          id SERIAL NOT NULL, 
          thread_id INT NOT NULL, 
          author_id INT NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          content TEXT NOT NULL, 
          approved BOOLEAN NOT NULL, 
          enabled BOOLEAN DEFAULT \'true\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_18589988E2904019 ON ideas_workshop_comment (thread_id)');
        $this->addSql('CREATE INDEX IDX_18589988F675F31B ON ideas_workshop_comment (author_id)');
        $this->addSql('CREATE UNIQUE INDEX threads_comments_uuid_unique ON ideas_workshop_comment (uuid)');
        $this->addSql('COMMENT ON COLUMN ideas_workshop_comment.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE ideas_workshop_need (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          enabled BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX need_name_unique ON ideas_workshop_need (name)');
        $this->addSql('CREATE TABLE ideas_workshop_consultation (
          id SERIAL NOT NULL, 
          response_time SMALLINT NOT NULL, 
          started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          ended_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          url VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          enabled BOOLEAN DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX consultation_enabled_unique ON ideas_workshop_consultation (enabled)');
        $this->addSql('CREATE TABLE ideas_workshop_vote (
          id SERIAL NOT NULL, 
          idea_id INT NOT NULL, 
          author_id INT NOT NULL, 
          type VARCHAR(10) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_9A9B53535B6FEF7D ON ideas_workshop_vote (idea_id)');
        $this->addSql('CREATE INDEX IDX_9A9B5353F675F31B ON ideas_workshop_vote (author_id)');
        $this->addSql('CREATE TABLE ideas_workshop_guideline (
          id SERIAL NOT NULL, 
          enabled BOOLEAN NOT NULL, 
          position SMALLINT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE ideas_workshop_theme (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          enabled BOOLEAN NOT NULL, 
          image_name VARCHAR(255) NOT NULL, 
          position SMALLINT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX theme_name_unique ON ideas_workshop_theme (name)');
        $this->addSql('CREATE TABLE ideas_workshop_answer (
          id SERIAL NOT NULL, 
          question_id INT NOT NULL, 
          idea_id INT NOT NULL, 
          content TEXT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_256A5D7B1E27F6BF ON ideas_workshop_answer (question_id)');
        $this->addSql('CREATE INDEX IDX_256A5D7B5B6FEF7D ON ideas_workshop_answer (idea_id)');
        $this->addSql('CREATE TABLE ideas_workshop_answer_user_documents (
          ideas_workshop_answer_id INT NOT NULL, 
          user_document_id INT NOT NULL, 
          PRIMARY KEY(
            ideas_workshop_answer_id, user_document_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_824E75E79C97E9FB ON ideas_workshop_answer_user_documents (ideas_workshop_answer_id)');
        $this->addSql('CREATE INDEX IDX_824E75E76A24B1A2 ON ideas_workshop_answer_user_documents (user_document_id)');
        $this->addSql('CREATE TABLE ideas_workshop_consultation_report (
          id SERIAL NOT NULL, 
          url VARCHAR(255) NOT NULL, 
          position SMALLINT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE ideas_workshop_category (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          enabled BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX category_name_unique ON ideas_workshop_category (name)');
        $this->addSql('CREATE TABLE home_blocks (
          id BIGSERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          position_name VARCHAR(30) NOT NULL, 
          title VARCHAR(70) NOT NULL, 
          subtitle VARCHAR(100) DEFAULT NULL, 
          type VARCHAR(10) NOT NULL, 
          link VARCHAR(255) NOT NULL, 
          display_filter BOOLEAN DEFAULT \'true\' NOT NULL, 
          display_titles BOOLEAN DEFAULT \'false\' NOT NULL, 
          display_block BOOLEAN DEFAULT \'true\' NOT NULL, 
          video_controls BOOLEAN DEFAULT \'false\' NOT NULL, 
          video_autoplay_loop BOOLEAN DEFAULT \'true\' NOT NULL, 
          title_cta VARCHAR(70) DEFAULT NULL, 
          color_cta VARCHAR(6) DEFAULT NULL, 
          bg_color VARCHAR(6) DEFAULT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EE9FCC5462CE4F5 ON home_blocks (position)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3EE9FCC54DBB5058 ON home_blocks (position_name)');
        $this->addSql('CREATE INDEX IDX_3EE9FCC5EA9FDD75 ON home_blocks (media_id)');
        $this->addSql('CREATE TABLE consular_district (
          id SERIAL NOT NULL, 
          countries TEXT NOT NULL, 
          cities TEXT NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          number SMALLINT NOT NULL, 
          points JSON DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX consular_district_code_unique ON consular_district (code)');
        $this->addSql('COMMENT ON COLUMN consular_district.countries IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN consular_district.cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE citizen_project_categories (
          id SERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX citizen_project_category_name_unique ON citizen_project_categories (name)');
        $this->addSql('CREATE UNIQUE INDEX citizen_project_category_slug_unique ON citizen_project_categories (slug)');
        $this->addSql('CREATE TABLE interactive_invitations (
          id SERIAL NOT NULL, 
          friend_age SMALLINT NOT NULL, 
          friend_gender VARCHAR(6) NOT NULL, 
          friend_position VARCHAR(50) DEFAULT NULL, 
          author_first_name VARCHAR(50) DEFAULT NULL, 
          author_last_name VARCHAR(50) DEFAULT NULL, 
          author_email_address VARCHAR(255) DEFAULT NULL, 
          mail_subject VARCHAR(100) DEFAULT NULL, 
          mail_body TEXT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX interactive_invitations_uuid_unique ON interactive_invitations (uuid)');
        $this->addSql('COMMENT ON COLUMN interactive_invitations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE interactive_invitation_has_choices (
          invitation_id INT NOT NULL, 
          choice_id INT NOT NULL, 
          PRIMARY KEY(invitation_id, choice_id)
        )');
        $this->addSql('CREATE INDEX IDX_31A811A2A35D7AF0 ON interactive_invitation_has_choices (invitation_id)');
        $this->addSql('CREATE INDEX IDX_31A811A2998666D1 ON interactive_invitation_has_choices (choice_id)');
        $this->addSql('CREATE TABLE interactive_choices (
          id SERIAL NOT NULL, 
          step SMALLINT NOT NULL, 
          content_key VARCHAR(30) NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          content TEXT NOT NULL, 
          uuid UUID NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX interactive_choices_uuid_unique ON interactive_choices (uuid)');
        $this->addSql('CREATE UNIQUE INDEX interactive_choices_content_key_unique ON interactive_choices (content_key)');
        $this->addSql('COMMENT ON COLUMN interactive_choices.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE legislative_candidates (
          id SMALLSERIAL NOT NULL, 
          district_zone_id SMALLINT DEFAULT NULL, 
          media_id BIGINT DEFAULT NULL, 
          position INT NOT NULL, 
          gender VARCHAR(6) NOT NULL, 
          email_address VARCHAR(100) DEFAULT NULL, 
          slug VARCHAR(100) NOT NULL, 
          facebook_page_url VARCHAR(255) DEFAULT NULL, 
          twitter_page_url VARCHAR(255) DEFAULT NULL, 
          donation_page_url VARCHAR(255) DEFAULT NULL, 
          website_url VARCHAR(255) DEFAULT NULL, 
          district_name VARCHAR(100) NOT NULL, 
          district_number SMALLINT NOT NULL, 
          geojson TEXT DEFAULT NULL, 
          description TEXT DEFAULT NULL, 
          career VARCHAR(255) NOT NULL, 
          status VARCHAR(20) DEFAULT \'none\' NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_AE55AF9B23F5C396 ON legislative_candidates (district_zone_id)');
        $this->addSql('CREATE INDEX IDX_AE55AF9BEA9FDD75 ON legislative_candidates (media_id)');
        $this->addSql('CREATE UNIQUE INDEX legislative_candidates_slug_unique ON legislative_candidates (slug)');
        $this->addSql('CREATE TABLE events_categories (
          id SERIAL NOT NULL, 
          event_group_category_id INT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_EF0AF3E9A267D842 ON events_categories (event_group_category_id)');
        $this->addSql('CREATE UNIQUE INDEX event_category_name_unique ON events_categories (name)');
        $this->addSql('CREATE UNIQUE INDEX event_category_slug_unique ON events_categories (slug)');
        $this->addSql('CREATE TABLE skills (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX skill_slug_unique ON skills (slug)');
        $this->addSql('CREATE TABLE designation (
          id SERIAL NOT NULL, 
          label VARCHAR(255) DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          zones TEXT DEFAULT NULL, 
          candidacy_start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          candidacy_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          vote_start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          vote_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          result_display_delay SMALLINT NOT NULL, 
          additional_round_duration SMALLINT NOT NULL, 
          lock_period_threshold SMALLINT NOT NULL, 
          limited BOOLEAN DEFAULT \'false\' NOT NULL, 
          denomination VARCHAR(255) DEFAULT \'désignation\' NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN designation.zones IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN designation.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE designation_referent_tag (
          designation_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(designation_id, referent_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_7538F35AFAC7D83F ON designation_referent_tag (designation_id)');
        $this->addSql('CREATE INDEX IDX_7538F35A9C262DB3 ON designation_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE voting_platform_election_round (
          id SERIAL NOT NULL, 
          election_id INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          is_active BOOLEAN DEFAULT \'true\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F15D87B7A708DAFF ON voting_platform_election_round (election_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_election_round.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_election_round_election_pool (
          election_round_id INT NOT NULL, 
          election_pool_id INT NOT NULL, 
          PRIMARY KEY(
            election_round_id, election_pool_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_E6665F19FCBF5E32 ON voting_platform_election_round_election_pool (election_round_id)');
        $this->addSql('CREATE INDEX IDX_E6665F19C1E98F21 ON voting_platform_election_round_election_pool (election_pool_id)');
        $this->addSql('CREATE TABLE voting_platform_candidate (
          id SERIAL NOT NULL, 
          candidate_group_id INT DEFAULT NULL, 
          adherent_id INT DEFAULT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          gender VARCHAR(255) NOT NULL, 
          biography TEXT DEFAULT NULL, 
          faith_statement TEXT DEFAULT NULL, 
          image_path VARCHAR(255) DEFAULT NULL, 
          additionally_elected BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_3F426D6D5F0A9B94 ON voting_platform_candidate (candidate_group_id)');
        $this->addSql('CREATE INDEX IDX_3F426D6D25F06C53 ON voting_platform_candidate (adherent_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_candidate.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_election (
          id SERIAL NOT NULL, 
          designation_id INT DEFAULT NULL, 
          status VARCHAR(255) NOT NULL, 
          closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          second_round_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          additional_places SMALLINT DEFAULT NULL, 
          additional_places_gender VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_4E144C94FAC7D83F ON voting_platform_election (designation_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_election.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_vote_result (
          id SERIAL NOT NULL, 
          election_round_id INT DEFAULT NULL, 
          voter_key VARCHAR(255) NOT NULL, 
          voted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_62C86890FCBF5E32 ON voting_platform_vote_result (election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote_result ON voting_platform_vote_result (voter_key, election_round_id)');
        $this->addSql('CREATE TABLE voting_platform_voters_list (
          id SERIAL NOT NULL, 
          election_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3C73500DA708DAFF ON voting_platform_voters_list (election_id)');
        $this->addSql('CREATE TABLE voting_platform_voters_list_voter (
          voters_list_id INT NOT NULL, 
          voter_id INT NOT NULL, 
          PRIMARY KEY(voters_list_id, voter_id)
        )');
        $this->addSql('CREATE INDEX IDX_7CC26956FB0C8C84 ON voting_platform_voters_list_voter (voters_list_id)');
        $this->addSql('CREATE INDEX IDX_7CC26956EBB4B8AD ON voting_platform_voters_list_voter (voter_id)');
        $this->addSql('CREATE TABLE voting_platform_vote (
          id SERIAL NOT NULL, 
          voter_id INT DEFAULT NULL, 
          election_round_id INT DEFAULT NULL, 
          voted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_DCBB2B7BEBB4B8AD ON voting_platform_vote (voter_id)');
        $this->addSql('CREATE INDEX IDX_DCBB2B7BFCBF5E32 ON voting_platform_vote (election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_vote ON voting_platform_vote (voter_id, election_round_id)');
        $this->addSql('CREATE TABLE voting_platform_candidate_group (
          id SERIAL NOT NULL, 
          election_pool_id INT DEFAULT NULL, 
          elected BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_2C1A353AC1E98F21 ON voting_platform_candidate_group (election_pool_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_candidate_group.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_voter (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          is_ghost BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AB02EC0225F06C53 ON voting_platform_voter (adherent_id)');
        $this->addSql('CREATE TABLE voting_platform_election_pool (
          id SERIAL NOT NULL, 
          election_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_7225D6EFA708DAFF ON voting_platform_election_pool (election_id)');
        $this->addSql('CREATE TABLE voting_platform_election_result (
          id SERIAL NOT NULL, 
          election_id INT DEFAULT NULL, 
          participated INT DEFAULT 0 NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67EFA0E4A708DAFF ON voting_platform_election_result (election_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_election_result.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_candidate_group_result (
          id SERIAL NOT NULL, 
          candidate_group_id INT DEFAULT NULL, 
          election_pool_result_id INT DEFAULT NULL, 
          total INT DEFAULT 0 NOT NULL, 
          total_mentions JSON DEFAULT NULL, 
          majority_mention VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_7249D5375F0A9B94 ON voting_platform_candidate_group_result (candidate_group_id)');
        $this->addSql('CREATE INDEX IDX_7249D537B5BA5CC5 ON voting_platform_candidate_group_result (election_pool_result_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_candidate_group_result.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_election_pool_result (
          id SERIAL NOT NULL, 
          election_pool_id INT DEFAULT NULL, 
          election_round_result_id INT DEFAULT NULL, 
          is_elected BOOLEAN DEFAULT \'false\' NOT NULL, 
          expressed INT DEFAULT 0 NOT NULL, 
          blank INT DEFAULT 0 NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_13C1C73FC1E98F21 ON voting_platform_election_pool_result (election_pool_id)');
        $this->addSql('CREATE INDEX IDX_13C1C73F8FFC0F0B ON voting_platform_election_pool_result (election_round_result_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_election_pool_result.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_election_round_result (
          id SERIAL NOT NULL, 
          election_round_id INT DEFAULT NULL, 
          election_result_id INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F2670966FCBF5E32 ON voting_platform_election_round_result (election_round_id)');
        $this->addSql('CREATE INDEX IDX_F267096619FCFB29 ON voting_platform_election_round_result (election_result_id)');
        $this->addSql('COMMENT ON COLUMN voting_platform_election_round_result.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE voting_platform_election_entity (
          id SERIAL NOT NULL, 
          committee_id INT DEFAULT NULL, 
          territorial_council_id INT DEFAULT NULL, 
          election_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_7AAD259FED1A100B ON voting_platform_election_entity (committee_id)');
        $this->addSql('CREATE INDEX IDX_7AAD259FAAA61A99 ON voting_platform_election_entity (territorial_council_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7AAD259FA708DAFF ON voting_platform_election_entity (election_id)');
        $this->addSql('CREATE TABLE voting_platform_vote_choice (
          id SERIAL NOT NULL, 
          vote_result_id INT DEFAULT NULL, 
          candidate_group_id INT DEFAULT NULL, 
          election_pool_id INT DEFAULT NULL, 
          is_blank BOOLEAN DEFAULT \'false\' NOT NULL, 
          mention VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_B009F31145EB7186 ON voting_platform_vote_choice (vote_result_id)');
        $this->addSql('CREATE INDEX IDX_B009F3115F0A9B94 ON voting_platform_vote_choice (candidate_group_id)');
        $this->addSql('CREATE INDEX IDX_B009F311C1E98F21 ON voting_platform_vote_choice (election_pool_id)');
        $this->addSql('CREATE TABLE events (
          id SERIAL NOT NULL, 
          organizer_id INT DEFAULT NULL, 
          committee_id INT DEFAULT NULL, 
          citizen_project_id INT DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          canonical_name VARCHAR(100) NOT NULL, 
          slug VARCHAR(130) NOT NULL, 
          description TEXT NOT NULL, 
          time_zone VARCHAR(50) NOT NULL, 
          begin_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          finish_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          participants_count SMALLINT NOT NULL, 
          status VARCHAR(20) NOT NULL, 
          published BOOLEAN DEFAULT \'true\' NOT NULL, 
          capacity INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          is_for_legislatives BOOLEAN DEFAULT \'false\', 
          category_id INT DEFAULT NULL, 
          invitations TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_5387574A876C4DDA ON events (organizer_id)');
        $this->addSql('CREATE INDEX IDX_5387574A12469DE2 ON events (category_id)');
        $this->addSql('CREATE INDEX IDX_5387574AED1A100B ON events (committee_id)');
        $this->addSql('CREATE INDEX IDX_5387574AB3584533 ON events (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_5387574A3826374D ON events (begin_at)');
        $this->addSql('CREATE INDEX IDX_5387574AFE28FD87 ON events (finish_at)');
        $this->addSql('CREATE INDEX IDX_5387574A7B00651C ON events (status)');
        $this->addSql('CREATE UNIQUE INDEX event_uuid_unique ON events (uuid)');
        $this->addSql('CREATE UNIQUE INDEX event_slug_unique ON events (slug)');
        $this->addSql('COMMENT ON COLUMN events.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN events.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN events.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN events.invitations IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE event_referent_tag (
          event_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(event_id, referent_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_D3C8F5BE71F7E88B ON event_referent_tag (event_id)');
        $this->addSql('CREATE INDEX IDX_D3C8F5BE9C262DB3 ON event_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE event_zone (
          base_event_id INT NOT NULL, 
          zone_id INT NOT NULL, 
          PRIMARY KEY(base_event_id, zone_id)
        )');
        $this->addSql('CREATE INDEX IDX_BF208CAC3B1C4B73 ON event_zone (base_event_id)');
        $this->addSql('CREATE INDEX IDX_BF208CAC9F2C3FAB ON event_zone (zone_id)');
        $this->addSql('CREATE TABLE event_user_documents (
          event_id INT NOT NULL, 
          user_document_id INT NOT NULL, 
          PRIMARY KEY(event_id, user_document_id)
        )');
        $this->addSql('CREATE INDEX IDX_7D14491F71F7E88B ON event_user_documents (event_id)');
        $this->addSql('CREATE INDEX IDX_7D14491F6A24B1A2 ON event_user_documents (user_document_id)');
        $this->addSql('CREATE TABLE committee_election (
          id SERIAL NOT NULL, 
          committee_id INT NOT NULL, 
          designation_id INT DEFAULT NULL, 
          adherent_notified BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_2CA406E5ED1A100B ON committee_election (committee_id)');
        $this->addSql('CREATE INDEX IDX_2CA406E5FAC7D83F ON committee_election (designation_id)');
        $this->addSql('COMMENT ON COLUMN committee_election.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_documents (
          id SERIAL NOT NULL, 
          original_name VARCHAR(200) NOT NULL, 
          extension VARCHAR(10) NOT NULL, 
          size INT NOT NULL, 
          mime_type VARCHAR(255) NOT NULL, 
          type VARCHAR(25) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX document_uuid_unique ON user_documents (uuid)');
        $this->addSql('COMMENT ON COLUMN user_documents.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE filesystem_file (
          id SERIAL NOT NULL, 
          created_by_id INT DEFAULT NULL, 
          updated_by_id INT DEFAULT NULL, 
          parent_id INT DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          type VARCHAR(20) NOT NULL, 
          displayed BOOLEAN DEFAULT \'true\' NOT NULL, 
          original_filename VARCHAR(255) DEFAULT NULL, 
          extension VARCHAR(10) DEFAULT NULL, 
          mime_type VARCHAR(75) DEFAULT NULL, 
          size INT DEFAULT NULL, 
          external_link VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_47F0AE28B03A8386 ON filesystem_file (created_by_id)');
        $this->addSql('CREATE INDEX IDX_47F0AE28896DBBDE ON filesystem_file (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_47F0AE28727ACA70 ON filesystem_file (parent_id)');
        $this->addSql('CREATE INDEX IDX_47F0AE288CDE5729 ON filesystem_file (type)');
        $this->addSql('CREATE INDEX IDX_47F0AE285E237E06 ON filesystem_file (name)');
        $this->addSql('CREATE UNIQUE INDEX filesystem_file_slug_unique ON filesystem_file (slug)');
        $this->addSql('COMMENT ON COLUMN filesystem_file.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE filesystem_file_permission (
          id SERIAL NOT NULL, 
          file_id INT NOT NULL, 
          name VARCHAR(50) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_BD623E4C93CB796C ON filesystem_file_permission (file_id)');
        $this->addSql('CREATE UNIQUE INDEX file_permission_unique ON filesystem_file_permission (file_id, name)');
        $this->addSql('CREATE TABLE adherent_change_email_token (
          id SERIAL NOT NULL, 
          adherent_uuid UUID NOT NULL, 
          value VARCHAR(40) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          email VARCHAR(255) NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_6F8B4B5AE7927C7477241BAC253ECC4 ON adherent_change_email_token (email, used_at, expired_at)');
        $this->addSql('COMMENT ON COLUMN adherent_change_email_token.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN adherent_change_email_token.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE proposals_themes (
          id SERIAL NOT NULL, 
          name VARCHAR(50) NOT NULL, 
          color VARCHAR(10) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE timeline_measures (
          id BIGSERIAL NOT NULL, 
          manifesto_id BIGINT NOT NULL, 
          link VARCHAR(255) DEFAULT NULL, 
          status VARCHAR(50) NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          major BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_BA475ED737E924 ON timeline_measures (manifesto_id)');
        $this->addSql('CREATE TABLE timeline_measures_profiles (
          measure_id BIGINT NOT NULL, 
          profile_id BIGINT NOT NULL, 
          PRIMARY KEY(measure_id, profile_id)
        )');
        $this->addSql('CREATE INDEX IDX_B83D81AE5DA37D00 ON timeline_measures_profiles (measure_id)');
        $this->addSql('CREATE INDEX IDX_B83D81AECCFA12B8 ON timeline_measures_profiles (profile_id)');
        $this->addSql('CREATE TABLE timeline_themes_measures (
          measure_id BIGINT NOT NULL, 
          theme_id BIGINT NOT NULL, 
          PRIMARY KEY(measure_id, theme_id)
        )');
        $this->addSql('CREATE INDEX IDX_EB8A7B0C5DA37D00 ON timeline_themes_measures (measure_id)');
        $this->addSql('CREATE INDEX IDX_EB8A7B0C59027487 ON timeline_themes_measures (theme_id)');
        $this->addSql('CREATE TABLE timeline_theme_translations (
          id SERIAL NOT NULL, 
          translatable_id BIGINT DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description TEXT NOT NULL, 
          locale VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F81F72932C2AC5D3 ON timeline_theme_translations (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX timeline_theme_translations_unique_translation ON timeline_theme_translations (translatable_id, locale)');
        $this->addSql('CREATE TABLE timeline_profiles (id BIGSERIAL NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE timeline_measure_translations (
          id SERIAL NOT NULL, 
          translatable_id BIGINT DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          locale VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_5C9EB6072C2AC5D3 ON timeline_measure_translations (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX timeline_measure_translations_unique_translation ON timeline_measure_translations (translatable_id, locale)');
        $this->addSql('CREATE TABLE timeline_manifestos (
          id BIGSERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          display_media BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_C6ED4403EA9FDD75 ON timeline_manifestos (media_id)');
        $this->addSql('CREATE TABLE timeline_manifesto_translations (
          id SERIAL NOT NULL, 
          translatable_id BIGINT DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description TEXT NOT NULL, 
          locale VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F7BD6C172C2AC5D3 ON timeline_manifesto_translations (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX timeline_manifesto_translations_unique_translation ON timeline_manifesto_translations (translatable_id, locale)');
        $this->addSql('CREATE TABLE timeline_themes (
          id BIGSERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          featured BOOLEAN DEFAULT \'false\' NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_8ADDB8F6EA9FDD75 ON timeline_themes (media_id)');
        $this->addSql('CREATE TABLE timeline_profile_translations (
          id SERIAL NOT NULL, 
          translatable_id BIGINT DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description TEXT NOT NULL, 
          locale VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_41B3A6DA2C2AC5D3 ON timeline_profile_translations (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX timeline_profile_translations_unique_translation ON timeline_profile_translations (translatable_id, locale)');
        $this->addSql('CREATE TABLE biography_executive_office_member (
          id SERIAL NOT NULL, 
          job VARCHAR(255) NOT NULL, 
          executive_officer BOOLEAN DEFAULT \'false\' NOT NULL, 
          deputy_general_delegate BOOLEAN DEFAULT \'false\' NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          published BOOLEAN DEFAULT \'false\' NOT NULL, 
          description VARCHAR(255) DEFAULT NULL, 
          content TEXT DEFAULT NULL, 
          facebook_profile VARCHAR(255) DEFAULT NULL, 
          twitter_profile VARCHAR(255) DEFAULT NULL, 
          instagram_profile VARCHAR(255) DEFAULT NULL, 
          linked_in_profile VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX executive_office_member_uuid_unique ON biography_executive_office_member (uuid)');
        $this->addSql('CREATE UNIQUE INDEX executive_office_member_slug_unique ON biography_executive_office_member (slug)');
        $this->addSql('COMMENT ON COLUMN biography_executive_office_member.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE event_group_category (
          id SERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX event_group_category_name_unique ON event_group_category (name)');
        $this->addSql('CREATE UNIQUE INDEX event_group_category_slug_unique ON event_group_category (slug)');
        $this->addSql('CREATE TABLE committee_candidacy (
          id SERIAL NOT NULL, 
          committee_election_id INT NOT NULL, 
          committee_membership_id INT NOT NULL, 
          invitation_id INT DEFAULT NULL, 
          binome_id INT DEFAULT NULL, 
          gender VARCHAR(255) NOT NULL, 
          biography TEXT DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          uuid UUID NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          faith_statement TEXT DEFAULT NULL, 
          is_public_faith_statement BOOLEAN DEFAULT \'false\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A04454D17F50A6 ON committee_candidacy (uuid)');
        $this->addSql('CREATE INDEX IDX_9A044544E891720 ON committee_candidacy (committee_election_id)');
        $this->addSql('CREATE INDEX IDX_9A04454FCC6DA91 ON committee_candidacy (committee_membership_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A04454A35D7AF0 ON committee_candidacy (invitation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A044548D4924C4 ON committee_candidacy (binome_id)');
        $this->addSql('COMMENT ON COLUMN committee_candidacy.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE reports (
          id SERIAL NOT NULL, 
          author_id INT DEFAULT NULL, 
          citizen_project_id INT DEFAULT NULL, 
          citizen_action_id INT DEFAULT NULL, 
          committee_id INT DEFAULT NULL, 
          community_event_id INT DEFAULT NULL, 
          idea_id INT DEFAULT NULL, 
          thread_id INT DEFAULT NULL, 
          thread_comment_id INT DEFAULT NULL, 
          reasons JSON NOT NULL, 
          comment TEXT DEFAULT NULL, 
          status VARCHAR(16) DEFAULT \'unresolved\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          resolved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F11FA745F675F31B ON reports (author_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745B3584533 ON reports (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745A2DD3412 ON reports (citizen_action_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745ED1A100B ON reports (committee_id)');
        $this->addSql('CREATE INDEX IDX_F11FA74583B12DAC ON reports (community_event_id)');
        $this->addSql('CREATE INDEX IDX_F11FA7455B6FEF7D ON reports (idea_id)');
        $this->addSql('CREATE INDEX IDX_F11FA745E2904019 ON reports (thread_id)');
        $this->addSql('CREATE INDEX IDX_F11FA7453A31E89B ON reports (thread_comment_id)');
        $this->addSql('CREATE INDEX report_status_idx ON reports (status)');
        $this->addSql('CREATE INDEX report_type_idx ON reports (type)');
        $this->addSql('CREATE UNIQUE INDEX report_uuid_unique ON reports (uuid)');
        $this->addSql('COMMENT ON COLUMN reports.reasons IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN reports.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE adherent_segment (
          id SERIAL NOT NULL, 
          author_id INT NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          member_ids TEXT NOT NULL, 
          segment_type VARCHAR(255) DEFAULT NULL, 
          synchronized BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          mailchimp_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_9DF0C7EBF675F31B ON adherent_segment (author_id)');
        $this->addSql('COMMENT ON COLUMN adherent_segment.member_ids IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN adherent_segment.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE adherent_messages (
          id SERIAL NOT NULL, 
          author_id INT DEFAULT NULL, 
          filter_id INT DEFAULT NULL, 
          label VARCHAR(255) NOT NULL, 
          subject VARCHAR(255) NOT NULL, 
          content TEXT NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          recipient_count INT DEFAULT NULL, 
          send_to_timeline BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_D187C183F675F31B ON adherent_messages (author_id)');
        $this->addSql('CREATE INDEX IDX_D187C183D395B25E ON adherent_messages (filter_id)');
        $this->addSql('COMMENT ON COLUMN adherent_messages.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE mailchimp_campaign_report (
          id SERIAL NOT NULL, 
          open_total INT NOT NULL, 
          open_unique INT NOT NULL, 
          open_rate INT NOT NULL, 
          last_open TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          click_total INT NOT NULL, 
          click_unique INT NOT NULL, 
          click_rate INT NOT NULL, 
          last_click TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          email_sent INT NOT NULL, 
          unsubscribed INT NOT NULL, 
          unsubscribed_rate INT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE mailchimp_campaign (
          id SERIAL NOT NULL, 
          report_id INT DEFAULT NULL, 
          message_id INT DEFAULT NULL, 
          external_id VARCHAR(255) DEFAULT NULL, 
          synchronized BOOLEAN DEFAULT \'false\' NOT NULL, 
          recipient_count INT DEFAULT NULL, 
          static_segment_id VARCHAR(255) DEFAULT NULL, 
          label VARCHAR(255) DEFAULT NULL, 
          status VARCHAR(255) NOT NULL, 
          detail VARCHAR(255) DEFAULT NULL, 
          city VARCHAR(255) DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CFABD3094BD2A4C0 ON mailchimp_campaign (report_id)');
        $this->addSql('CREATE INDEX IDX_CFABD309537A1329 ON mailchimp_campaign (message_id)');
        $this->addSql('CREATE TABLE mailchimp_campaign_mailchimp_segment (
          mailchimp_campaign_id INT NOT NULL, 
          mailchimp_segment_id INT NOT NULL, 
          PRIMARY KEY(
            mailchimp_campaign_id, mailchimp_segment_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_901CE107828112CC ON mailchimp_campaign_mailchimp_segment (mailchimp_campaign_id)');
        $this->addSql('CREATE INDEX IDX_901CE107D21E482E ON mailchimp_campaign_mailchimp_segment (mailchimp_segment_id)');
        $this->addSql('CREATE TABLE adherent_message_filters (
          id SERIAL NOT NULL, 
          adherent_segment_id INT DEFAULT NULL, 
          committee_id INT DEFAULT NULL, 
          user_list_definition_id INT DEFAULT NULL, 
          referent_tag_id INT DEFAULT NULL, 
          citizen_project_id INT DEFAULT NULL, 
          zone_id INT DEFAULT NULL, 
          territorial_council_id INT DEFAULT NULL, 
          political_committee_id INT DEFAULT NULL, 
          synchronized BOOLEAN DEFAULT \'false\' NOT NULL, 
          dtype VARCHAR(255) NOT NULL, 
          gender VARCHAR(255) DEFAULT NULL, 
          age_min INT DEFAULT NULL, 
          age_max INT DEFAULT NULL, 
          first_name VARCHAR(255) DEFAULT NULL, 
          last_name VARCHAR(255) DEFAULT NULL, 
          city VARCHAR(255) DEFAULT NULL, 
          interests JSON DEFAULT NULL, 
          registered_since DATE DEFAULT NULL, 
          registered_until DATE DEFAULT NULL, 
          contact_only_volunteers BOOLEAN DEFAULT \'false\', 
          contact_only_running_mates BOOLEAN DEFAULT \'false\', 
          include_adherents_no_committee BOOLEAN DEFAULT NULL, 
          include_adherents_in_committee BOOLEAN DEFAULT NULL, 
          include_committee_supervisors BOOLEAN DEFAULT NULL, 
          include_committee_provisional_supervisors BOOLEAN DEFAULT NULL, 
          include_committee_hosts BOOLEAN DEFAULT NULL, 
          include_citizen_project_hosts BOOLEAN DEFAULT NULL, 
          mandate VARCHAR(255) DEFAULT NULL, 
          political_function VARCHAR(255) DEFAULT NULL, 
          label VARCHAR(255) DEFAULT NULL, 
          insee_code VARCHAR(255) DEFAULT NULL, 
          contact_volunteer_team BOOLEAN DEFAULT \'false\', 
          contact_running_mate_team BOOLEAN DEFAULT \'false\', 
          contact_adherents BOOLEAN DEFAULT \'false\', 
          contact_newsletter BOOLEAN DEFAULT \'false\', 
          postal_code VARCHAR(10) DEFAULT NULL, 
          qualities TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_28CA9F94FAF04979 ON adherent_message_filters (adherent_segment_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94ED1A100B ON adherent_message_filters (committee_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94F74563E3 ON adherent_message_filters (user_list_definition_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F949C262DB3 ON adherent_message_filters (referent_tag_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94B3584533 ON adherent_message_filters (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F949F2C3FAB ON adherent_message_filters (zone_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94AAA61A99 ON adherent_message_filters (territorial_council_id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94C7A72 ON adherent_message_filters (political_committee_id)');
        $this->addSql('COMMENT ON COLUMN adherent_message_filters.interests IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN adherent_message_filters.qualities IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE referent_user_filter_referent_tag (
          referent_user_filter_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            referent_user_filter_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_F2BB20FEEFAB50C4 ON referent_user_filter_referent_tag (referent_user_filter_id)');
        $this->addSql('CREATE INDEX IDX_F2BB20FE9C262DB3 ON referent_user_filter_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE pages (
          id BIGSERIAL NOT NULL, 
          header_media_id BIGINT DEFAULT NULL, 
          media_id BIGINT DEFAULT NULL, 
          layout VARCHAR(255) DEFAULT \'default\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          twitter_description VARCHAR(255) DEFAULT NULL, 
          keywords VARCHAR(255) DEFAULT NULL, 
          content TEXT NOT NULL, 
          amp_content TEXT DEFAULT NULL, 
          display_media BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2074E575989D9B62 ON pages (slug)');
        $this->addSql('CREATE INDEX IDX_2074E5755B42DC0F ON pages (header_media_id)');
        $this->addSql('CREATE INDEX IDX_2074E575EA9FDD75 ON pages (media_id)');
        $this->addSql('CREATE TABLE procuration_proxies (
          id SERIAL NOT NULL, 
          reliability SMALLINT NOT NULL, 
          reliability_description VARCHAR(30) DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          first_names VARCHAR(100) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          city_insee VARCHAR(15) DEFAULT NULL, 
          city_name VARCHAR(255) DEFAULT NULL, 
          state VARCHAR(255) DEFAULT NULL, 
          country VARCHAR(2) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          birthdate DATE DEFAULT NULL, 
          vote_postal_code VARCHAR(15) DEFAULT NULL, 
          vote_city_insee VARCHAR(15) DEFAULT NULL, 
          vote_city_name VARCHAR(255) DEFAULT NULL, 
          vote_country VARCHAR(2) NOT NULL, 
          vote_office VARCHAR(50) NOT NULL, 
          disabled BOOLEAN NOT NULL, 
          proxies_count SMALLINT DEFAULT 1 NOT NULL, 
          french_request_available BOOLEAN DEFAULT \'true\' NOT NULL, 
          foreign_request_available BOOLEAN DEFAULT \'true\' NOT NULL, 
          reachable BOOLEAN DEFAULT \'false\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN procuration_proxies.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE procuration_proxies_to_election_rounds (
          procuration_proxy_id INT NOT NULL, 
          election_round_id INT NOT NULL, 
          PRIMARY KEY(
            procuration_proxy_id, election_round_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_D075F5A9E15E419B ON procuration_proxies_to_election_rounds (procuration_proxy_id)');
        $this->addSql('CREATE INDEX IDX_D075F5A9FCBF5E32 ON procuration_proxies_to_election_rounds (election_round_id)');
        $this->addSql('CREATE TABLE vote_place (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          alias VARCHAR(255) DEFAULT NULL, 
          code VARCHAR(10) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code TEXT DEFAULT NULL, 
          city VARCHAR(50) DEFAULT NULL, 
          country VARCHAR(2) NOT NULL, 
          holder_office_available BOOLEAN NOT NULL, 
          substitute_office_available BOOLEAN NOT NULL, 
          enabled BOOLEAN DEFAULT \'true\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2574310677153098 ON vote_place (code)');
        $this->addSql('CREATE TABLE administrator_export_history (
          id SERIAL NOT NULL, 
          administrator_id INT NOT NULL, 
          route_name VARCHAR(255) NOT NULL, 
          parameters JSON NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_10499F014B09E92C ON administrator_export_history (administrator_id)');
        $this->addSql('COMMENT ON COLUMN administrator_export_history.parameters IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE adherent_certification_histories (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          administrator_id INT DEFAULT NULL, 
          action VARCHAR(20) NOT NULL, 
          date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX adherent_certification_histories_adherent_id_idx ON adherent_certification_histories (adherent_id)');
        $this->addSql('CREATE INDEX adherent_certification_histories_administrator_id_idx ON adherent_certification_histories (administrator_id)');
        $this->addSql('CREATE INDEX adherent_certification_histories_date_idx ON adherent_certification_histories (date)');
        $this->addSql('COMMENT ON COLUMN adherent_certification_histories.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE committee_merge_histories (
          id SERIAL NOT NULL, 
          source_committee_id INT NOT NULL, 
          destination_committee_id INT NOT NULL, 
          merged_by_id INT DEFAULT NULL, 
          reverted_by_id INT DEFAULT NULL, 
          date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          reverted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_BB95FBBC50FA8329 ON committee_merge_histories (merged_by_id)');
        $this->addSql('CREATE INDEX IDX_BB95FBBCA8E1562 ON committee_merge_histories (reverted_by_id)');
        $this->addSql('CREATE INDEX committee_merge_histories_source_committee_id_idx ON committee_merge_histories (source_committee_id)');
        $this->addSql('CREATE INDEX committee_merge_histories_destination_committee_id_idx ON committee_merge_histories (destination_committee_id)');
        $this->addSql('CREATE INDEX committee_merge_histories_date_idx ON committee_merge_histories (date)');
        $this->addSql('COMMENT ON COLUMN committee_merge_histories.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN committee_merge_histories.reverted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE committee_merge_histories_merged_memberships (
          committee_merge_history_id INT NOT NULL, 
          committee_membership_id INT NOT NULL, 
          PRIMARY KEY(
            committee_merge_history_id, committee_membership_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_CB8E336F9379ED92 ON committee_merge_histories_merged_memberships (committee_merge_history_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB8E336FFCC6DA91 ON committee_merge_histories_merged_memberships (committee_membership_id)');
        $this->addSql('CREATE TABLE adherent_email_subscription_histories (
          id SERIAL NOT NULL, 
          subscription_type_id INT NOT NULL, 
          adherent_uuid UUID NOT NULL, 
          action VARCHAR(32) NOT NULL, 
          date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_51AD8354B6596C08 ON adherent_email_subscription_histories (subscription_type_id)');
        $this->addSql('CREATE INDEX adherent_email_subscription_histories_adherent_uuid_idx ON adherent_email_subscription_histories (adherent_uuid)');
        $this->addSql('CREATE INDEX adherent_email_subscription_histories_adherent_action_idx ON adherent_email_subscription_histories (action)');
        $this->addSql('CREATE INDEX adherent_email_subscription_histories_adherent_date_idx ON adherent_email_subscription_histories (date)');
        $this->addSql('COMMENT ON COLUMN adherent_email_subscription_histories.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN adherent_email_subscription_histories.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE adherent_email_subscription_history_referent_tag (
          email_subscription_history_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            email_subscription_history_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_6FFBE6E88FCB8132 ON adherent_email_subscription_history_referent_tag (email_subscription_history_id)');
        $this->addSql('CREATE INDEX IDX_6FFBE6E89C262DB3 ON adherent_email_subscription_history_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE committees_membership_histories (
          id SERIAL NOT NULL, 
          committee_id INT DEFAULT NULL, 
          adherent_uuid UUID NOT NULL, 
          action VARCHAR(10) NOT NULL, 
          privilege VARCHAR(10) NOT NULL, 
          date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_4BBAE2C7ED1A100B ON committees_membership_histories (committee_id)');
        $this->addSql('CREATE INDEX committees_membership_histories_adherent_uuid_idx ON committees_membership_histories (adherent_uuid)');
        $this->addSql('CREATE INDEX committees_membership_histories_action_idx ON committees_membership_histories (action)');
        $this->addSql('CREATE INDEX committees_membership_histories_date_idx ON committees_membership_histories (date)');
        $this->addSql('COMMENT ON COLUMN committees_membership_histories.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN committees_membership_histories.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE committee_membership_history_referent_tag (
          committee_membership_history_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            committee_membership_history_id, 
            referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_B6A8C718123C64CE ON committee_membership_history_referent_tag (
          committee_membership_history_id
        )');
        $this->addSql('CREATE INDEX IDX_B6A8C7189C262DB3 ON committee_membership_history_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE user_authorizations (
          id SERIAL NOT NULL, 
          user_id INT DEFAULT NULL, 
          client_id INT DEFAULT NULL, 
          scopes JSON NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_40448230A76ED395 ON user_authorizations (user_id)');
        $this->addSql('CREATE INDEX IDX_4044823019EB6921 ON user_authorizations (client_id)');
        $this->addSql('CREATE UNIQUE INDEX user_authorizations_unique ON user_authorizations (user_id, client_id)');
        $this->addSql('COMMENT ON COLUMN user_authorizations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE oauth_refresh_tokens (
          id SERIAL NOT NULL, 
          access_token_id INT DEFAULT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_5AB6872CCB2688 ON oauth_refresh_tokens (access_token_id)');
        $this->addSql('CREATE UNIQUE INDEX oauth_refresh_tokens_uuid_unique ON oauth_refresh_tokens (uuid)');
        $this->addSql('CREATE UNIQUE INDEX oauth_refresh_tokens_identifier_unique ON oauth_refresh_tokens (identifier)');
        $this->addSql('COMMENT ON COLUMN oauth_refresh_tokens.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth_refresh_tokens.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE oauth_access_tokens (
          id SERIAL NOT NULL, 
          client_id INT NOT NULL, 
          user_id INT DEFAULT NULL, 
          device_id INT DEFAULT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          scopes JSON NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CA42527C19EB6921 ON oauth_access_tokens (client_id)');
        $this->addSql('CREATE INDEX IDX_CA42527CA76ED395 ON oauth_access_tokens (user_id)');
        $this->addSql('CREATE INDEX IDX_CA42527C94A4C7D4 ON oauth_access_tokens (device_id)');
        $this->addSql('CREATE UNIQUE INDEX oauth_access_tokens_uuid_unique ON oauth_access_tokens (uuid)');
        $this->addSql('CREATE UNIQUE INDEX oauth_access_tokens_identifier_unique ON oauth_access_tokens (identifier)');
        $this->addSql('COMMENT ON COLUMN oauth_access_tokens.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth_access_tokens.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE oauth_clients (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          redirect_uris JSON NOT NULL, 
          secret VARCHAR(255) NOT NULL, 
          allowed_grant_types TEXT NOT NULL, 
          supported_scopes TEXT DEFAULT NULL, 
          ask_user_for_authorization BOOLEAN DEFAULT \'true\' NOT NULL, 
          uuid UUID NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX oauth_clients_uuid_unique ON oauth_clients (uuid)');
        $this->addSql('COMMENT ON COLUMN oauth_clients.allowed_grant_types IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN oauth_clients.supported_scopes IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN oauth_clients.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE oauth_auth_codes (
          id SERIAL NOT NULL, 
          client_id INT NOT NULL, 
          user_id INT DEFAULT NULL, 
          device_id INT DEFAULT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          scopes JSON NOT NULL, 
          redirect_uri TEXT NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_BB493F8319EB6921 ON oauth_auth_codes (client_id)');
        $this->addSql('CREATE INDEX IDX_BB493F83A76ED395 ON oauth_auth_codes (user_id)');
        $this->addSql('CREATE INDEX IDX_BB493F8394A4C7D4 ON oauth_auth_codes (device_id)');
        $this->addSql('CREATE UNIQUE INDEX oauth_auth_codes_uuid_unique ON oauth_auth_codes (uuid)');
        $this->addSql('CREATE UNIQUE INDEX oauth_auth_codes_identifier_unique ON oauth_auth_codes (identifier)');
        $this->addSql('COMMENT ON COLUMN oauth_auth_codes.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN oauth_auth_codes.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE referent_space_access_information (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          previous_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          last_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CD8FDF4825F06C53 ON referent_space_access_information (adherent_id)');
        $this->addSql('COMMENT ON COLUMN referent_space_access_information.previous_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN referent_space_access_information.last_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE territorial_council_convocation (
          id SERIAL NOT NULL, 
          territorial_council_id INT DEFAULT NULL, 
          political_committee_id INT DEFAULT NULL, 
          created_by_id INT DEFAULT NULL, 
          meeting_start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          meeting_end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          description TEXT NOT NULL, 
          mode VARCHAR(255) NOT NULL, 
          meeting_url VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A9919BF0AAA61A99 ON territorial_council_convocation (territorial_council_id)');
        $this->addSql('CREATE INDEX IDX_A9919BF0C7A72 ON territorial_council_convocation (political_committee_id)');
        $this->addSql('CREATE INDEX IDX_A9919BF0B03A8386 ON territorial_council_convocation (created_by_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_convocation.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN territorial_council_convocation.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN territorial_council_convocation.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE political_committee_feed_item (
          id SERIAL NOT NULL, 
          political_committee_id INT NOT NULL, 
          author_id INT DEFAULT NULL, 
          is_locked BOOLEAN DEFAULT \'false\' NOT NULL, 
          content TEXT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_54369E83C7A72 ON political_committee_feed_item (political_committee_id)');
        $this->addSql('CREATE INDEX IDX_54369E83F675F31B ON political_committee_feed_item (author_id)');
        $this->addSql('COMMENT ON COLUMN political_committee_feed_item.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_official_report (
          id SERIAL NOT NULL, 
          political_committee_id INT NOT NULL, 
          author_id INT DEFAULT NULL, 
          created_by_id INT DEFAULT NULL, 
          updated_by_id INT DEFAULT NULL, 
          name VARCHAR(50) NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_8D80D385C7A72 ON territorial_council_official_report (political_committee_id)');
        $this->addSql('CREATE INDEX IDX_8D80D385F675F31B ON territorial_council_official_report (author_id)');
        $this->addSql('CREATE INDEX IDX_8D80D385B03A8386 ON territorial_council_official_report (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8D80D385896DBBDE ON territorial_council_official_report (updated_by_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_official_report.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_membership_log (
          id SERIAL NOT NULL, 
          adherent_id INT NOT NULL, 
          type VARCHAR(20) NOT NULL, 
          description VARCHAR(500) NOT NULL, 
          quality_name VARCHAR(50) NOT NULL, 
          actual_territorial_council VARCHAR(255) DEFAULT NULL, 
          actual_quality_names TEXT DEFAULT NULL, 
          found_territorial_councils TEXT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          is_resolved BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_2F6D242025F06C53 ON territorial_council_membership_log (adherent_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_membership_log.actual_quality_names IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN territorial_council_membership_log.found_territorial_councils IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE territorial_council_election (
          id SERIAL NOT NULL, 
          territorial_council_id INT DEFAULT NULL, 
          election_poll_id INT DEFAULT NULL, 
          designation_id INT DEFAULT NULL, 
          meeting_start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          meeting_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          description TEXT DEFAULT NULL, 
          questions TEXT DEFAULT NULL, 
          election_mode VARCHAR(255) DEFAULT NULL, 
          meeting_url VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_14CBC36BAAA61A99 ON territorial_council_election (territorial_council_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14CBC36B8649F5F1 ON territorial_council_election (election_poll_id)');
        $this->addSql('CREATE INDEX IDX_14CBC36BFAC7D83F ON territorial_council_election (designation_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_election.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN territorial_council_election.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN territorial_council_election.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE political_committee (
          id SERIAL NOT NULL, 
          territorial_council_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          is_active BOOLEAN DEFAULT \'false\' NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39FAEE955E237E06 ON political_committee (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39FAEE95AAA61A99 ON political_committee (territorial_council_id)');
        $this->addSql('COMMENT ON COLUMN political_committee.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_feed_item (
          id SERIAL NOT NULL, 
          territorial_council_id INT NOT NULL, 
          author_id INT DEFAULT NULL, 
          is_locked BOOLEAN DEFAULT \'false\' NOT NULL, 
          content TEXT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_45241D62AAA61A99 ON territorial_council_feed_item (territorial_council_id)');
        $this->addSql('CREATE INDEX IDX_45241D62F675F31B ON territorial_council_feed_item (author_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_feed_item.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_election_poll_choice (
          id SERIAL NOT NULL, 
          election_poll_id INT NOT NULL, 
          value VARCHAR(255) NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_63EBCF6B8649F5F1 ON territorial_council_election_poll_choice (election_poll_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_election_poll_choice.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_election_poll_vote (
          id SERIAL NOT NULL, 
          choice_id INT DEFAULT NULL, 
          membership_id INT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_BCDA0C15998666D1 ON territorial_council_election_poll_vote (choice_id)');
        $this->addSql('CREATE INDEX IDX_BCDA0C151FB354CD ON territorial_council_election_poll_vote (membership_id)');
        $this->addSql('CREATE TABLE territorial_council_election_poll (
          id SERIAL NOT NULL, 
          gender VARCHAR(255) NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN territorial_council_election_poll.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_official_report_document (
          id SERIAL NOT NULL, 
          created_by_id INT DEFAULT NULL, 
          report_id INT DEFAULT NULL, 
          filename VARCHAR(36) NOT NULL, 
          extension VARCHAR(10) NOT NULL, 
          mime_type VARCHAR(30) NOT NULL, 
          version SMALLINT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_78C1161DB03A8386 ON territorial_council_official_report_document (created_by_id)');
        $this->addSql('CREATE INDEX IDX_78C1161D4BD2A4C0 ON territorial_council_official_report_document (report_id)');
        $this->addSql('CREATE TABLE territorial_council (
          id SERIAL NOT NULL, 
          current_designation_id INT DEFAULT NULL, 
          name VARCHAR(255) NOT NULL, 
          codes VARCHAR(50) NOT NULL, 
          is_active BOOLEAN DEFAULT \'true\' NOT NULL, 
          uuid UUID NOT NULL, 
          mailchimp_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_B6DCA2A5B4D2A5D1 ON territorial_council (current_designation_id)');
        $this->addSql('CREATE UNIQUE INDEX territorial_council_uuid_unique ON territorial_council (uuid)');
        $this->addSql('CREATE UNIQUE INDEX territorial_council_name_unique ON territorial_council (name)');
        $this->addSql('CREATE UNIQUE INDEX territorial_council_codes_unique ON territorial_council (codes)');
        $this->addSql('COMMENT ON COLUMN territorial_council.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_referent_tag (
          territorial_council_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            territorial_council_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_78DBEB90AAA61A99 ON territorial_council_referent_tag (territorial_council_id)');
        $this->addSql('CREATE INDEX IDX_78DBEB909C262DB3 ON territorial_council_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE territorial_council_zone (
          territorial_council_id INT NOT NULL, 
          zone_id INT NOT NULL, 
          PRIMARY KEY(territorial_council_id, zone_id)
        )');
        $this->addSql('CREATE INDEX IDX_9467B41EAAA61A99 ON territorial_council_zone (territorial_council_id)');
        $this->addSql('CREATE INDEX IDX_9467B41E9F2C3FAB ON territorial_council_zone (zone_id)');
        $this->addSql('CREATE TABLE political_committee_quality (
          id SERIAL NOT NULL, 
          political_committee_membership_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_243D6D3A78632915 ON political_committee_quality (
          political_committee_membership_id
        )');
        $this->addSql('CREATE TABLE territorial_council_quality (
          id SERIAL NOT NULL, 
          territorial_council_membership_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          zone VARCHAR(255) NOT NULL, 
          joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_C018E022E797FAB0 ON territorial_council_quality (
          territorial_council_membership_id
        )');
        $this->addSql('CREATE TABLE territorial_council_candidacy_invitation (
          id SERIAL NOT NULL, 
          membership_id INT NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          declined_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_DA86009A1FB354CD ON territorial_council_candidacy_invitation (membership_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_candidacy_invitation.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE territorial_council_candidacy (
          id SERIAL NOT NULL, 
          election_id INT NOT NULL, 
          membership_id INT NOT NULL, 
          invitation_id INT DEFAULT NULL, 
          binome_id INT DEFAULT NULL, 
          gender VARCHAR(255) NOT NULL, 
          biography TEXT DEFAULT NULL, 
          quality VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          faith_statement TEXT DEFAULT NULL, 
          is_public_faith_statement BOOLEAN DEFAULT \'false\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39885B6D17F50A6 ON territorial_council_candidacy (uuid)');
        $this->addSql('CREATE INDEX IDX_39885B6A708DAFF ON territorial_council_candidacy (election_id)');
        $this->addSql('CREATE INDEX IDX_39885B61FB354CD ON territorial_council_candidacy (membership_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39885B6A35D7AF0 ON territorial_council_candidacy (invitation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39885B68D4924C4 ON territorial_council_candidacy (binome_id)');
        $this->addSql('COMMENT ON COLUMN territorial_council_candidacy.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE institutional_events_categories (
          id SERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX institutional_event_category_name_unique ON institutional_events_categories (name)');
        $this->addSql('CREATE UNIQUE INDEX institutional_event_slug_unique ON institutional_events_categories (slug)');
        $this->addSql('CREATE TABLE elected_representative_zone (
          id SERIAL NOT NULL, 
          category_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          code VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_C52FC4A712469DE2 ON elected_representative_zone (category_id)');
        $this->addSql('CREATE INDEX elected_repr_zone_code ON elected_representative_zone (code)');
        $this->addSql('CREATE UNIQUE INDEX elected_representative_zone_name_category_unique ON elected_representative_zone (name, category_id)');
        $this->addSql('CREATE TABLE elected_representative_zone_referent_tag (
          elected_representative_zone_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            elected_representative_zone_id, 
            referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_D2B7A8C5BE31A103 ON elected_representative_zone_referent_tag (elected_representative_zone_id)');
        $this->addSql('CREATE INDEX IDX_D2B7A8C59C262DB3 ON elected_representative_zone_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE elected_representative_zone_parent (
          child_id INT NOT NULL, 
          parent_id INT NOT NULL, 
          PRIMARY KEY(child_id, parent_id)
        )');
        $this->addSql('CREATE INDEX IDX_CECA906FDD62C21B ON elected_representative_zone_parent (child_id)');
        $this->addSql('CREATE INDEX IDX_CECA906F727ACA70 ON elected_representative_zone_parent (parent_id)');
        $this->addSql('CREATE TABLE elected_representative_political_function (
          id SERIAL NOT NULL, 
          elected_representative_id INT NOT NULL, 
          mandate_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          clarification VARCHAR(255) DEFAULT NULL, 
          on_going BOOLEAN DEFAULT \'true\' NOT NULL, 
          begin_at DATE NOT NULL, 
          finish_at DATE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_303BAF41D38DA5D3 ON elected_representative_political_function (elected_representative_id)');
        $this->addSql('CREATE INDEX IDX_303BAF416C1129CD ON elected_representative_political_function (mandate_id)');
        $this->addSql('CREATE TABLE elected_representative_social_network_link (
          id SERIAL NOT NULL, 
          elected_representative_id INT NOT NULL, 
          url VARCHAR(255) NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_231377B5D38DA5D3 ON elected_representative_social_network_link (elected_representative_id)');
        $this->addSql('CREATE UNIQUE INDEX social_network_elected_representative_unique ON elected_representative_social_network_link (type, elected_representative_id)');
        $this->addSql('CREATE TABLE elected_representative_zone_category (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX elected_representative_zone_category_name_unique ON elected_representative_zone_category (name)');
        $this->addSql('CREATE TABLE elected_representative_mandate (
          id SERIAL NOT NULL, 
          zone_id INT DEFAULT NULL, 
          geo_zone_id INT DEFAULT NULL, 
          elected_representative_id INT NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          is_elected BOOLEAN DEFAULT \'false\' NOT NULL, 
          on_going BOOLEAN DEFAULT \'true\' NOT NULL, 
          begin_at DATE NOT NULL, 
          finish_at DATE DEFAULT NULL, 
          political_affiliation VARCHAR(10) NOT NULL, 
          la_remsupport VARCHAR(255) DEFAULT NULL, 
          number SMALLINT DEFAULT 1 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_386091469F2C3FAB ON elected_representative_mandate (zone_id)');
        $this->addSql('CREATE INDEX IDX_38609146283AB2A9 ON elected_representative_mandate (geo_zone_id)');
        $this->addSql('CREATE INDEX IDX_38609146D38DA5D3 ON elected_representative_mandate (elected_representative_id)');
        $this->addSql('CREATE TABLE elected_representative_user_list_definition_history (
          id SERIAL NOT NULL, 
          elected_representative_id INT NOT NULL, 
          user_list_definition_id INT NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          administrator_id INT DEFAULT NULL, 
          action VARCHAR(20) NOT NULL, 
          date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_1ECF7566D38DA5D3 ON elected_representative_user_list_definition_history (elected_representative_id)');
        $this->addSql('CREATE INDEX IDX_1ECF7566F74563E3 ON elected_representative_user_list_definition_history (user_list_definition_id)');
        $this->addSql('CREATE INDEX IDX_1ECF756625F06C53 ON elected_representative_user_list_definition_history (adherent_id)');
        $this->addSql('CREATE INDEX IDX_1ECF75664B09E92C ON elected_representative_user_list_definition_history (administrator_id)');
        $this->addSql('COMMENT ON COLUMN elected_representative_user_list_definition_history.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE elected_representative_sponsorship (
          id SERIAL NOT NULL, 
          elected_representative_id INT NOT NULL, 
          presidential_election_year INT NOT NULL, 
          candidate VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CA6D486D38DA5D3 ON elected_representative_sponsorship (elected_representative_id)');
        $this->addSql('CREATE TABLE elected_representative_label (
          id SERIAL NOT NULL, 
          elected_representative_id INT NOT NULL, 
          name VARCHAR(50) NOT NULL, 
          on_going BOOLEAN DEFAULT \'true\' NOT NULL, 
          begin_year INT DEFAULT NULL, 
          finish_year INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_D8143704D38DA5D3 ON elected_representative_label (elected_representative_id)');
        $this->addSql('CREATE TABLE elected_representative (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          gender VARCHAR(10) DEFAULT NULL, 
          birth_date DATE NOT NULL, 
          birth_place VARCHAR(255) DEFAULT NULL, 
          contact_email VARCHAR(255) DEFAULT NULL, 
          contact_phone VARCHAR(35) DEFAULT NULL, 
          has_followed_training BOOLEAN DEFAULT \'false\' NOT NULL, 
          email_unsubscribed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          email_unsubscribed BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF51F0FD25F06C53 ON elected_representative (adherent_id)');
        $this->addSql('COMMENT ON COLUMN elected_representative.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN elected_representative.contact_phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE elected_representative_user_list_definition (
          elected_representative_id INT NOT NULL, 
          user_list_definition_id INT NOT NULL, 
          PRIMARY KEY(
            elected_representative_id, user_list_definition_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_A9C53A24D38DA5D3 ON elected_representative_user_list_definition (elected_representative_id)');
        $this->addSql('CREATE INDEX IDX_A9C53A24F74563E3 ON elected_representative_user_list_definition (user_list_definition_id)');
        $this->addSql('CREATE TABLE newsletter_subscriptions (
          id BIGSERIAL NOT NULL, 
          email VARCHAR(100) NOT NULL, 
          postal_code VARCHAR(11) DEFAULT NULL, 
          country VARCHAR(2) DEFAULT NULL, 
          from_event BOOLEAN DEFAULT \'false\' NOT NULL, 
          confirmed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID DEFAULT NULL, 
          token UUID DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3C13B0BE7927C74 ON newsletter_subscriptions (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3C13B0BD17F50A6 ON newsletter_subscriptions (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3C13B0B5F37A13B ON newsletter_subscriptions (token)');
        $this->addSql('COMMENT ON COLUMN newsletter_subscriptions.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN newsletter_subscriptions.token IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE clarifications (
          id BIGSERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          twitter_description VARCHAR(255) DEFAULT NULL, 
          keywords VARCHAR(255) DEFAULT NULL, 
          content TEXT NOT NULL, 
          amp_content TEXT DEFAULT NULL, 
          display_media BOOLEAN NOT NULL, 
          published BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FAB8972989D9B62 ON clarifications (slug)');
        $this->addSql('CREATE INDEX IDX_2FAB8972EA9FDD75 ON clarifications (media_id)');
        $this->addSql('CREATE TABLE ton_macron_friend_invitations (
          id SERIAL NOT NULL, 
          friend_first_name VARCHAR(50) NOT NULL, 
          friend_age SMALLINT NOT NULL, 
          friend_gender VARCHAR(6) NOT NULL, 
          friend_position VARCHAR(50) NOT NULL, 
          friend_email_address VARCHAR(255) DEFAULT NULL, 
          author_first_name VARCHAR(50) DEFAULT NULL, 
          author_last_name VARCHAR(50) DEFAULT NULL, 
          author_email_address VARCHAR(255) DEFAULT NULL, 
          mail_subject VARCHAR(100) DEFAULT NULL, 
          mail_body TEXT DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX ton_macron_friend_invitations_uuid_unique ON ton_macron_friend_invitations (uuid)');
        $this->addSql('COMMENT ON COLUMN ton_macron_friend_invitations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE ton_macron_friend_invitation_has_choices (
          invitation_id INT NOT NULL, 
          choice_id INT NOT NULL, 
          PRIMARY KEY(invitation_id, choice_id)
        )');
        $this->addSql('CREATE INDEX IDX_BB3BCAEEA35D7AF0 ON ton_macron_friend_invitation_has_choices (invitation_id)');
        $this->addSql('CREATE INDEX IDX_BB3BCAEE998666D1 ON ton_macron_friend_invitation_has_choices (choice_id)');
        $this->addSql('CREATE TABLE adherent_reset_password_tokens (
          id SERIAL NOT NULL, 
          adherent_uuid UUID NOT NULL, 
          value VARCHAR(40) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX adherent_reset_password_token_unique ON adherent_reset_password_tokens (value)');
        $this->addSql('CREATE UNIQUE INDEX adherent_reset_password_token_account_unique ON adherent_reset_password_tokens (value, adherent_uuid)');
        $this->addSql('COMMENT ON COLUMN adherent_reset_password_tokens.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN adherent_reset_password_tokens.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE facebook_videos (
          id SERIAL NOT NULL, 
          facebook_url VARCHAR(255) NOT NULL, 
          twitter_url VARCHAR(255) DEFAULT NULL, 
          description VARCHAR(255) NOT NULL, 
          author VARCHAR(100) NOT NULL, 
          position INT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          published BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE committees (
          id SERIAL NOT NULL, 
          current_designation_id INT DEFAULT NULL, 
          description TEXT NOT NULL, 
          facebook_page_url VARCHAR(255) DEFAULT NULL, 
          twitter_nickname VARCHAR(255) DEFAULT NULL, 
          name_locked BOOLEAN DEFAULT \'false\' NOT NULL, 
          status VARCHAR(20) NOT NULL, 
          approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          refused_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          created_by UUID DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          members_count SMALLINT NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          canonical_name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          mailchimp_id INT DEFAULT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude DOUBLE PRECISION DEFAULT NULL, 
          address_longitude DOUBLE PRECISION DEFAULT NULL, 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A36198C6B4D2A5D1 ON committees (current_designation_id)');
        $this->addSql('CREATE INDEX committee_status_idx ON committees (status)');
        $this->addSql('CREATE UNIQUE INDEX committee_uuid_unique ON committees (uuid)');
        $this->addSql('CREATE UNIQUE INDEX committee_canonical_name_unique ON committees (canonical_name)');
        $this->addSql('CREATE UNIQUE INDEX committee_slug_unique ON committees (slug)');
        $this->addSql('COMMENT ON COLUMN committees.created_by IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN committees.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN committees.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN committees.address_latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN committees.address_longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE committee_referent_tag (
          committee_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(committee_id, referent_tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_285EB1C5ED1A100B ON committee_referent_tag (committee_id)');
        $this->addSql('CREATE INDEX IDX_285EB1C59C262DB3 ON committee_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE committee_zone (
          committee_id INT NOT NULL, 
          zone_id INT NOT NULL, 
          PRIMARY KEY(committee_id, zone_id)
        )');
        $this->addSql('CREATE INDEX IDX_37C5F224ED1A100B ON committee_zone (committee_id)');
        $this->addSql('CREATE INDEX IDX_37C5F2249F2C3FAB ON committee_zone (zone_id)');
        $this->addSql('CREATE TABLE custom_search_results (
          id BIGSERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          keywords VARCHAR(255) DEFAULT NULL, 
          url VARCHAR(255) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_38973E54EA9FDD75 ON custom_search_results (media_id)');
        $this->addSql('CREATE TABLE algolia_candidature (id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN algolia_candidature.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE donation_tags (
          id SERIAL NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          color VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX donation_tag_label_unique ON donation_tags (label)');
        $this->addSql('CREATE TABLE turnkey_projects (
          id SERIAL NOT NULL, 
          category_id INT DEFAULT NULL, 
          name VARCHAR(255) NOT NULL, 
          canonical_name VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          subtitle VARCHAR(255) NOT NULL, 
          problem_description TEXT DEFAULT NULL, 
          proposed_solution TEXT DEFAULT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          youtube_id VARCHAR(11) DEFAULT NULL, 
          is_pinned BOOLEAN DEFAULT \'false\' NOT NULL, 
          is_favorite BOOLEAN DEFAULT \'false\' NOT NULL, 
          is_approved BOOLEAN DEFAULT \'false\' NOT NULL, 
          position SMALLINT DEFAULT 1 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CB66CFAE12469DE2 ON turnkey_projects (category_id)');
        $this->addSql('CREATE UNIQUE INDEX turnkey_project_canonical_name_unique ON turnkey_projects (canonical_name)');
        $this->addSql('CREATE UNIQUE INDEX turnkey_project_slug_unique ON turnkey_projects (slug)');
        $this->addSql('CREATE TABLE turnkey_project_turnkey_project_file (
          turnkey_project_id INT NOT NULL, 
          turnkey_project_file_id INT NOT NULL, 
          PRIMARY KEY(
            turnkey_project_id, turnkey_project_file_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_67BF8377B5315DF4 ON turnkey_project_turnkey_project_file (turnkey_project_id)');
        $this->addSql('CREATE INDEX IDX_67BF83777D06E1CD ON turnkey_project_turnkey_project_file (turnkey_project_file_id)');
        $this->addSql('CREATE TABLE application_request_volunteer (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          displayed BOOLEAN DEFAULT \'true\' NOT NULL, 
          custom_technical_skills VARCHAR(255) DEFAULT NULL, 
          is_previous_campaign_member BOOLEAN NOT NULL, 
          previous_campaign_details TEXT DEFAULT NULL, 
          share_associative_commitment BOOLEAN NOT NULL, 
          associative_commitment_details TEXT DEFAULT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          favorite_cities TEXT NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          city VARCHAR(20) DEFAULT NULL, 
          city_name VARCHAR(50) NOT NULL, 
          country VARCHAR(2) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          profession VARCHAR(255) NOT NULL, 
          custom_favorite_theme TEXT DEFAULT NULL, 
          taken_for_city VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_1139657025F06C53 ON application_request_volunteer (adherent_id)');
        $this->addSql('COMMENT ON COLUMN application_request_volunteer.favorite_cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN application_request_volunteer.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN application_request_volunteer.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE volunteer_request_technical_skill (
          volunteer_request_id INT NOT NULL, 
          technical_skill_id INT NOT NULL, 
          PRIMARY KEY(
            volunteer_request_id, technical_skill_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_7F8C5C1EB8D6887 ON volunteer_request_technical_skill (volunteer_request_id)');
        $this->addSql('CREATE INDEX IDX_7F8C5C1EE98F0EFD ON volunteer_request_technical_skill (technical_skill_id)');
        $this->addSql('CREATE TABLE volunteer_request_theme (
          volunteer_request_id INT NOT NULL, 
          theme_id INT NOT NULL, 
          PRIMARY KEY(volunteer_request_id, theme_id)
        )');
        $this->addSql('CREATE INDEX IDX_5427AF53B8D6887 ON volunteer_request_theme (volunteer_request_id)');
        $this->addSql('CREATE INDEX IDX_5427AF5359027487 ON volunteer_request_theme (theme_id)');
        $this->addSql('CREATE TABLE volunteer_request_application_request_tag (
          volunteer_request_id INT NOT NULL, 
          application_request_tag_id INT NOT NULL, 
          PRIMARY KEY(
            volunteer_request_id, application_request_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_6F3FA269B8D6887 ON volunteer_request_application_request_tag (volunteer_request_id)');
        $this->addSql('CREATE INDEX IDX_6F3FA2699644FEDA ON volunteer_request_application_request_tag (application_request_tag_id)');
        $this->addSql('CREATE TABLE volunteer_request_referent_tag (
          volunteer_request_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            volunteer_request_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_DA291742B8D6887 ON volunteer_request_referent_tag (volunteer_request_id)');
        $this->addSql('CREATE INDEX IDX_DA2917429C262DB3 ON volunteer_request_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE application_request_tag (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE application_request_technical_skill (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          display BOOLEAN DEFAULT \'true\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE application_request_theme (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          display BOOLEAN DEFAULT \'true\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE application_request_running_mate (
          id SERIAL NOT NULL, 
          adherent_id INT DEFAULT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          displayed BOOLEAN DEFAULT \'true\' NOT NULL, 
          curriculum_name VARCHAR(255) DEFAULT NULL, 
          is_local_association_member BOOLEAN DEFAULT \'false\' NOT NULL, 
          local_association_domain TEXT DEFAULT NULL, 
          is_political_activist BOOLEAN DEFAULT \'false\' NOT NULL, 
          political_activist_details TEXT DEFAULT NULL, 
          is_previous_elected_official BOOLEAN DEFAULT \'false\' NOT NULL, 
          previous_elected_official_details TEXT DEFAULT NULL, 
          favorite_theme_details TEXT NOT NULL, 
          project_details TEXT NOT NULL, 
          professional_assets TEXT NOT NULL, 
          first_name VARCHAR(255) NOT NULL, 
          last_name VARCHAR(255) NOT NULL, 
          favorite_cities TEXT NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          city VARCHAR(20) DEFAULT NULL, 
          city_name VARCHAR(50) NOT NULL, 
          country VARCHAR(2) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          profession VARCHAR(255) NOT NULL, 
          custom_favorite_theme TEXT DEFAULT NULL, 
          taken_for_city VARCHAR(255) DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_D1D6095625F06C53 ON application_request_running_mate (adherent_id)');
        $this->addSql('COMMENT ON COLUMN application_request_running_mate.favorite_cities IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN application_request_running_mate.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('COMMENT ON COLUMN application_request_running_mate.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE running_mate_request_theme (
          running_mate_request_id INT NOT NULL, 
          theme_id INT NOT NULL, 
          PRIMARY KEY(
            running_mate_request_id, theme_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_A7326227CEDF4387 ON running_mate_request_theme (running_mate_request_id)');
        $this->addSql('CREATE INDEX IDX_A732622759027487 ON running_mate_request_theme (theme_id)');
        $this->addSql('CREATE TABLE running_mate_request_application_request_tag (
          running_mate_request_id INT NOT NULL, 
          application_request_tag_id INT NOT NULL, 
          PRIMARY KEY(
            running_mate_request_id, application_request_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_9D534FCFCEDF4387 ON running_mate_request_application_request_tag (running_mate_request_id)');
        $this->addSql('CREATE INDEX IDX_9D534FCF9644FEDA ON running_mate_request_application_request_tag (application_request_tag_id)');
        $this->addSql('CREATE TABLE running_mate_request_referent_tag (
          running_mate_request_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            running_mate_request_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_53AB4FABCEDF4387 ON running_mate_request_referent_tag (running_mate_request_id)');
        $this->addSql('CREATE INDEX IDX_53AB4FAB9C262DB3 ON running_mate_request_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE committee_candidacy_invitation (
          id SERIAL NOT NULL, 
          membership_id INT NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          declined_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_368B01611FB354CD ON committee_candidacy_invitation (membership_id)');
        $this->addSql('COMMENT ON COLUMN committee_candidacy_invitation.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE emails (
          id SERIAL NOT NULL, 
          message_class VARCHAR(55) DEFAULT NULL, 
          sender VARCHAR(100) NOT NULL, 
          recipients TEXT DEFAULT NULL, 
          request_payload TEXT NOT NULL, 
          response_payload TEXT DEFAULT NULL, 
          delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN emails.recipients IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN emails.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE epci (
          id SERIAL NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          surface DOUBLE PRECISION NOT NULL, 
          department_code VARCHAR(10) NOT NULL, 
          department_name VARCHAR(255) NOT NULL, 
          region_code VARCHAR(10) NOT NULL, 
          region_name VARCHAR(255) NOT NULL, 
          city_insee VARCHAR(10) NOT NULL, 
          city_code VARCHAR(10) NOT NULL, 
          city_name VARCHAR(255) NOT NULL, 
          city_full_name VARCHAR(255) NOT NULL, 
          city_dep VARCHAR(255) NOT NULL, 
          city_siren VARCHAR(255) NOT NULL, 
          code_arr VARCHAR(255) NOT NULL, 
          code_cant VARCHAR(255) NOT NULL, 
          population INT DEFAULT NULL, 
          epci_dep VARCHAR(255) NOT NULL, 
          epci_siren VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          insee VARCHAR(255) NOT NULL, 
          fiscal VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE citizen_project_committee_supports (
          id SERIAL NOT NULL, 
          citizen_project_id INT DEFAULT NULL, 
          committee_id INT DEFAULT NULL, 
          status VARCHAR(20) NOT NULL, 
          requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F694C3BCB3584533 ON citizen_project_committee_supports (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_F694C3BCED1A100B ON citizen_project_committee_supports (committee_id)');
        $this->addSql('CREATE TABLE chez_vous_measures (
          id SERIAL NOT NULL, 
          city_id INT NOT NULL, 
          type_id INT NOT NULL, 
          payload JSON DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_E6E8973E8BAC62AF ON chez_vous_measures (city_id)');
        $this->addSql('CREATE INDEX IDX_E6E8973EC54C8C93 ON chez_vous_measures (type_id)');
        $this->addSql('CREATE UNIQUE INDEX chez_vous_measures_city_type_unique ON chez_vous_measures (city_id, type_id)');
        $this->addSql('COMMENT ON COLUMN chez_vous_measures.payload IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE chez_vous_regions (
          id SERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          code VARCHAR(10) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A6C12FCC77153098 ON chez_vous_regions (code)');
        $this->addSql('CREATE TABLE chez_vous_markers (
          id SERIAL NOT NULL, 
          city_id INT NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          latitude DOUBLE PRECISION NOT NULL, 
          longitude DOUBLE PRECISION NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_452F890F8BAC62AF ON chez_vous_markers (city_id)');
        $this->addSql('COMMENT ON COLUMN chez_vous_markers.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN chez_vous_markers.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE chez_vous_measure_types (
          id SERIAL NOT NULL, 
          code VARCHAR(255) NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          source_link VARCHAR(255) DEFAULT NULL, 
          source_label VARCHAR(255) DEFAULT NULL, 
          oldolf_link VARCHAR(255) DEFAULT NULL, 
          eligibility_link VARCHAR(255) DEFAULT NULL, 
          citizen_projects_link VARCHAR(255) DEFAULT NULL, 
          ideas_workshop_link VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B80D46F577153098 ON chez_vous_measure_types (code)');
        $this->addSql('CREATE TABLE chez_vous_cities (
          id SERIAL NOT NULL, 
          department_id INT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          postal_codes JSON NOT NULL, 
          insee_code VARCHAR(10) NOT NULL, 
          latitude DOUBLE PRECISION NOT NULL, 
          longitude DOUBLE PRECISION NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A42D9BED15A3C1BC ON chez_vous_cities (insee_code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A42D9BED989D9B62 ON chez_vous_cities (slug)');
        $this->addSql('CREATE INDEX IDX_A42D9BEDAE80F5DF ON chez_vous_cities (department_id)');
        $this->addSql('COMMENT ON COLUMN chez_vous_cities.postal_codes IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN chez_vous_cities.latitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('COMMENT ON COLUMN chez_vous_cities.longitude IS \'(DC2Type:geo_point)\'');
        $this->addSql('CREATE TABLE chez_vous_departments (
          id SERIAL NOT NULL, 
          region_id INT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          label VARCHAR(100) DEFAULT NULL, 
          code VARCHAR(10) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29E7DD5777153098 ON chez_vous_departments (code)');
        $this->addSql('CREATE INDEX IDX_29E7DD5798260155 ON chez_vous_departments (region_id)');
        $this->addSql('CREATE TABLE procuration_requests (
          id SERIAL NOT NULL, 
          found_proxy_id INT DEFAULT NULL, 
          procuration_request_found_by_id INT DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          first_names VARCHAR(100) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          city_insee VARCHAR(15) DEFAULT NULL, 
          city_name VARCHAR(255) DEFAULT NULL, 
          state VARCHAR(255) DEFAULT NULL, 
          country VARCHAR(2) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          birthdate DATE DEFAULT NULL, 
          vote_postal_code VARCHAR(15) DEFAULT NULL, 
          vote_city_insee VARCHAR(15) DEFAULT NULL, 
          vote_city_name VARCHAR(255) DEFAULT NULL, 
          vote_country VARCHAR(2) NOT NULL, 
          vote_office VARCHAR(50) NOT NULL, 
          reason VARCHAR(15) NOT NULL, 
          processed BOOLEAN NOT NULL, 
          processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          reminded INT NOT NULL, 
          request_from_france BOOLEAN DEFAULT \'true\' NOT NULL, 
          reachable BOOLEAN DEFAULT \'false\' NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_9769FD842F1B6663 ON procuration_requests (found_proxy_id)');
        $this->addSql('CREATE INDEX IDX_9769FD84888FDEEE ON procuration_requests (procuration_request_found_by_id)');
        $this->addSql('COMMENT ON COLUMN procuration_requests.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE procuration_requests_to_election_rounds (
          procuration_request_id INT NOT NULL, 
          election_round_id INT NOT NULL, 
          PRIMARY KEY(
            procuration_request_id, election_round_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_A47BBD53128D9C53 ON procuration_requests_to_election_rounds (procuration_request_id)');
        $this->addSql('CREATE INDEX IDX_A47BBD53FCBF5E32 ON procuration_requests_to_election_rounds (election_round_id)');
        $this->addSql('CREATE TABLE citizen_action_categories (
          id SERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX citizen_action_category_name_unique ON citizen_action_categories (name)');
        $this->addSql('CREATE UNIQUE INDEX citizen_action_category_slug_unique ON citizen_action_categories (slug)');
        $this->addSql('CREATE TABLE programmatic_foundation_measure (
          id SERIAL NOT NULL, 
          sub_approach_id INT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          content TEXT NOT NULL, 
          is_leading BOOLEAN NOT NULL, 
          is_expanded BOOLEAN NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_213A5F1EF0ED738A ON programmatic_foundation_measure (sub_approach_id)');
        $this->addSql('COMMENT ON COLUMN programmatic_foundation_measure.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE programmatic_foundation_measure_tag (
          measure_id INT NOT NULL, 
          tag_id INT NOT NULL, 
          PRIMARY KEY(measure_id, tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_F004297F5DA37D00 ON programmatic_foundation_measure_tag (measure_id)');
        $this->addSql('CREATE INDEX IDX_F004297FBAD26311 ON programmatic_foundation_measure_tag (tag_id)');
        $this->addSql('CREATE TABLE programmatic_foundation_tag (
          id SERIAL NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12127927EA750E8 ON programmatic_foundation_tag (label)');
        $this->addSql('CREATE TABLE programmatic_foundation_approach (
          id SERIAL NOT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          content TEXT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN programmatic_foundation_approach.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE programmatic_foundation_project (
          id SERIAL NOT NULL, 
          measure_id INT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          content TEXT NOT NULL, 
          city VARCHAR(255) NOT NULL, 
          is_expanded BOOLEAN NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_8E8E96D55DA37D00 ON programmatic_foundation_project (measure_id)');
        $this->addSql('COMMENT ON COLUMN programmatic_foundation_project.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE programmatic_foundation_project_tag (
          project_id INT NOT NULL, 
          tag_id INT NOT NULL, 
          PRIMARY KEY(project_id, tag_id)
        )');
        $this->addSql('CREATE INDEX IDX_9F63872166D1F9C ON programmatic_foundation_project_tag (project_id)');
        $this->addSql('CREATE INDEX IDX_9F63872BAD26311 ON programmatic_foundation_project_tag (tag_id)');
        $this->addSql('CREATE TABLE programmatic_foundation_sub_approach (
          id SERIAL NOT NULL, 
          approach_id INT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          subtitle VARCHAR(255) DEFAULT NULL, 
          content TEXT DEFAULT NULL, 
          is_expanded BOOLEAN NOT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_735C1D0115140614 ON programmatic_foundation_sub_approach (approach_id)');
        $this->addSql('COMMENT ON COLUMN programmatic_foundation_sub_approach.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE web_hooks (
          id SERIAL NOT NULL, 
          client_id INT NOT NULL, 
          event VARCHAR(64) NOT NULL, 
          service VARCHAR(64) DEFAULT NULL, 
          callbacks JSON NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_CDB836AD19EB6921 ON web_hooks (client_id)');
        $this->addSql('CREATE UNIQUE INDEX web_hook_uuid_unique ON web_hooks (uuid)');
        $this->addSql('CREATE UNIQUE INDEX web_hook_event_client_id_unique ON web_hooks (event, client_id)');
        $this->addSql('COMMENT ON COLUMN web_hooks.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE proposals (
          id SERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          published BOOLEAN NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          twitter_description VARCHAR(255) DEFAULT NULL, 
          keywords VARCHAR(255) DEFAULT NULL, 
          content TEXT NOT NULL, 
          amp_content TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5BA3A8F989D9B62 ON proposals (slug)');
        $this->addSql('CREATE INDEX IDX_A5BA3A8FEA9FDD75 ON proposals (media_id)');
        $this->addSql('CREATE TABLE proposal_proposal_theme (
          proposal_id INT NOT NULL, 
          proposal_theme_id INT NOT NULL, 
          PRIMARY KEY(proposal_id, proposal_theme_id)
        )');
        $this->addSql('CREATE INDEX IDX_6B80CE41F4792058 ON proposal_proposal_theme (proposal_id)');
        $this->addSql('CREATE INDEX IDX_6B80CE41B85948AF ON proposal_proposal_theme (proposal_theme_id)');
        $this->addSql('CREATE TABLE order_articles (
          id SERIAL NOT NULL, 
          media_id BIGINT DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          published BOOLEAN NOT NULL, 
          display_media BOOLEAN NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          twitter_description VARCHAR(255) DEFAULT NULL, 
          keywords VARCHAR(255) DEFAULT NULL, 
          content TEXT NOT NULL, 
          amp_content TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E25D3D9989D9B62 ON order_articles (slug)');
        $this->addSql('CREATE INDEX IDX_5E25D3D9EA9FDD75 ON order_articles (media_id)');
        $this->addSql('CREATE TABLE order_section_order_article (
          order_article_id INT NOT NULL, 
          order_section_id INT NOT NULL, 
          PRIMARY KEY(
            order_article_id, order_section_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_A956D4E4C14E7BC9 ON order_section_order_article (order_article_id)');
        $this->addSql('CREATE INDEX IDX_A956D4E46BF91E2F ON order_section_order_article (order_section_id)');
        $this->addSql('CREATE TABLE turnkey_projects_files (
          id SERIAL NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          path VARCHAR(255) NOT NULL, 
          extension VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX turnkey_projects_file_slug_extension ON turnkey_projects_files (slug, extension)');
        $this->addSql('CREATE TABLE articles (
          id BIGSERIAL NOT NULL, 
          category_id INT DEFAULT NULL, 
          media_id BIGINT DEFAULT NULL, 
          published_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          title VARCHAR(100) NOT NULL, 
          slug VARCHAR(100) NOT NULL, 
          description VARCHAR(255) NOT NULL, 
          twitter_description VARCHAR(255) DEFAULT NULL, 
          keywords VARCHAR(255) DEFAULT NULL, 
          content TEXT NOT NULL, 
          amp_content TEXT DEFAULT NULL, 
          display_media BOOLEAN NOT NULL, 
          published BOOLEAN NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFDD3168989D9B62 ON articles (slug)');
        $this->addSql('CREATE INDEX IDX_BFDD316812469DE2 ON articles (category_id)');
        $this->addSql('CREATE INDEX IDX_BFDD3168EA9FDD75 ON articles (media_id)');
        $this->addSql('CREATE TABLE article_proposal_theme (
          article_id BIGINT NOT NULL, 
          proposal_theme_id INT NOT NULL, 
          PRIMARY KEY(article_id, proposal_theme_id)
        )');
        $this->addSql('CREATE INDEX IDX_F6B9A2217294869C ON article_proposal_theme (article_id)');
        $this->addSql('CREATE INDEX IDX_F6B9A221B85948AF ON article_proposal_theme (proposal_theme_id)');
        $this->addSql('CREATE TABLE social_share_categories (
          id BIGSERIAL NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          position SMALLINT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE mailchimp_segment (
          id SERIAL NOT NULL, 
          list VARCHAR(255) NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          external_id VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE unregistrations (
          id SERIAL NOT NULL, 
          excluded_by_id INT DEFAULT NULL, 
          uuid UUID NOT NULL, 
          postal_code VARCHAR(15) DEFAULT NULL, 
          reasons JSON NOT NULL, 
          comment TEXT DEFAULT NULL, 
          registered_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          unregistered_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          is_adherent BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F9E4AA0C5B30B80B ON unregistrations (excluded_by_id)');
        $this->addSql('COMMENT ON COLUMN unregistrations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN unregistrations.reasons IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE unregistration_referent_tag (
          unregistration_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            unregistration_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_59B7AC414D824CA ON unregistration_referent_tag (unregistration_id)');
        $this->addSql('CREATE INDEX IDX_59B7AC49C262DB3 ON unregistration_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE republican_silence (
          id SERIAL NOT NULL, 
          begin_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          finish_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE republican_silence_referent_tag (
          republican_silence_id INT NOT NULL, 
          referent_tag_id INT NOT NULL, 
          PRIMARY KEY(
            republican_silence_id, referent_tag_id
          )
        )');
        $this->addSql('CREATE INDEX IDX_543DED2612359909 ON republican_silence_referent_tag (republican_silence_id)');
        $this->addSql('CREATE INDEX IDX_543DED269C262DB3 ON republican_silence_referent_tag (referent_tag_id)');
        $this->addSql('CREATE TABLE cities (
          id SERIAL NOT NULL, 
          department_id INT DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          insee_code VARCHAR(10) NOT NULL, 
          postal_codes TEXT NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D95DB16B15A3C1BC ON cities (insee_code)');
        $this->addSql('CREATE INDEX IDX_D95DB16BAE80F5DF ON cities (department_id)');
        $this->addSql('COMMENT ON COLUMN cities.postal_codes IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE geo_data (id SERIAL NOT NULL, geo_shape Geometry NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX geo_data_geo_shape_idx ON geo_data USING GIST (geo_shape)');
        $this->addSql('COMMENT ON COLUMN geo_data.geo_shape IS \'(DC2Type:geometry)\'');
        $this->addSql('CREATE TABLE department (
          id SERIAL NOT NULL, 
          region_id INT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          label VARCHAR(100) DEFAULT NULL, 
          code VARCHAR(10) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CD1DE18A77153098 ON department (code)');
        $this->addSql('CREATE INDEX IDX_CD1DE18A98260155 ON department (region_id)');
        $this->addSql('CREATE TABLE failed_login_attempt (
          id SERIAL NOT NULL, 
          signature VARCHAR(255) NOT NULL, 
          at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          extra JSON NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN failed_login_attempt.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE image (
          id SERIAL NOT NULL, 
          uuid UUID NOT NULL, 
          extension VARCHAR(10) NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045FD17F50A6 ON image (uuid)');
        $this->addSql('COMMENT ON COLUMN image.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE newsletter_invitations (
          id SERIAL NOT NULL, 
          first_name VARCHAR(50) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          email VARCHAR(255) NOT NULL, 
          client_ip VARCHAR(50) DEFAULT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          uuid UUID NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN newsletter_invitations.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE member_summary_mission_types (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX member_summary_mission_type_name_unique ON member_summary_mission_types (name)');
        $this->addSql('CREATE TABLE member_summary_job_experiences (
          id SERIAL NOT NULL, 
          summary_id INT DEFAULT NULL, 
          company VARCHAR(255) NOT NULL, 
          position VARCHAR(255) NOT NULL, 
          location VARCHAR(255) NOT NULL, 
          website VARCHAR(255) DEFAULT NULL, 
          company_facebook_page VARCHAR(255) DEFAULT NULL, 
          company_twitter_nickname VARCHAR(255) DEFAULT NULL, 
          contract VARCHAR(255) NOT NULL, 
          duration VARCHAR(255) NOT NULL, 
          description TEXT DEFAULT NULL, 
          display_order SMALLINT DEFAULT 1 NOT NULL, 
          on_going BOOLEAN DEFAULT \'false\' NOT NULL, 
          started_at DATE NOT NULL, 
          ended_at DATE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_72DD8B7F2AC2D45C ON member_summary_job_experiences (summary_id)');
        $this->addSql('CREATE TABLE member_summary_trainings (
          id SERIAL NOT NULL, 
          summary_id INT DEFAULT NULL, 
          organization VARCHAR(255) NOT NULL, 
          diploma VARCHAR(255) NOT NULL, 
          study_field VARCHAR(255) NOT NULL, 
          description TEXT DEFAULT NULL, 
          extra_curricular TEXT DEFAULT NULL, 
          display_order SMALLINT DEFAULT 1 NOT NULL, 
          on_going BOOLEAN DEFAULT \'false\' NOT NULL, 
          started_at DATE NOT NULL, 
          ended_at DATE DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_C101987B2AC2D45C ON member_summary_trainings (summary_id)');
        $this->addSql('CREATE TABLE member_summary_languages (
          id SERIAL NOT NULL, 
          summary_id INT DEFAULT NULL, 
          code VARCHAR(255) NOT NULL, 
          level VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_70C88322AC2D45C ON member_summary_languages (summary_id)');
        $this->addSql('CREATE TABLE vote_result (
          id SERIAL NOT NULL, 
          election_round_id INT NOT NULL, 
          created_by_id INT DEFAULT NULL, 
          updated_by_id INT DEFAULT NULL, 
          city_id INT DEFAULT NULL, 
          vote_place_id INT DEFAULT NULL, 
          registered INT NOT NULL, 
          abstentions INT NOT NULL, 
          participated INT NOT NULL, 
          expressed INT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          type VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_1F8DB349FCBF5E32 ON vote_result (election_round_id)');
        $this->addSql('CREATE INDEX IDX_1F8DB349B03A8386 ON vote_result (created_by_id)');
        $this->addSql('CREATE INDEX IDX_1F8DB349896DBBDE ON vote_result (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_1F8DB3498BAC62AF ON vote_result (city_id)');
        $this->addSql('CREATE INDEX IDX_1F8DB349F3F90B30 ON vote_result (vote_place_id)');
        $this->addSql('CREATE UNIQUE INDEX city_vote_result_city_round_unique ON vote_result (city_id, election_round_id)');
        $this->addSql('CREATE UNIQUE INDEX vote_place_result_city_round_unique ON vote_result (
          vote_place_id, election_round_id
        )');
        $this->addSql('CREATE TABLE election_city_manager (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN election_city_manager.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE election_city_candidate (
          id SERIAL NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          gender VARCHAR(6) DEFAULT NULL, 
          email VARCHAR(255) DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          profile VARCHAR(255) DEFAULT NULL, 
          investiture_type VARCHAR(255) DEFAULT NULL, 
          political_scheme VARCHAR(255) DEFAULT NULL, 
          alliances VARCHAR(255) DEFAULT NULL, 
          agreement BOOLEAN DEFAULT \'false\' NOT NULL, 
          eligible_advisers_count INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN election_city_candidate.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE vote_result_list (
          id SERIAL NOT NULL, 
          list_collection_id INT DEFAULT NULL, 
          label VARCHAR(255) NOT NULL, 
          nuance VARCHAR(255) DEFAULT NULL, 
          adherent_count INT DEFAULT NULL, 
          eligible_count INT DEFAULT NULL, 
          position INT DEFAULT NULL, 
          candidate_first_name VARCHAR(255) DEFAULT NULL, 
          candidate_last_name VARCHAR(255) DEFAULT NULL, 
          outgoing_mayor BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_677ED502DB567AF4 ON vote_result_list (list_collection_id)');
        $this->addSql('CREATE TABLE election_city_contact (
          id SERIAL NOT NULL, 
          city_id INT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          function VARCHAR(255) DEFAULT NULL, 
          phone VARCHAR(35) DEFAULT NULL, 
          caller VARCHAR(255) DEFAULT NULL, 
          done BOOLEAN DEFAULT \'false\' NOT NULL, 
          comment TEXT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_D04AFB68BAC62AF ON election_city_contact (city_id)');
        $this->addSql('COMMENT ON COLUMN election_city_contact.phone IS \'(DC2Type:phone_number)\'');
        $this->addSql('CREATE TABLE election_city_card (
          id SERIAL NOT NULL, 
          city_id INT NOT NULL, 
          first_candidate_id INT DEFAULT NULL, 
          headquarters_manager_id INT DEFAULT NULL, 
          politic_manager_id INT DEFAULT NULL, 
          task_force_manager_id INT DEFAULT NULL, 
          candidate_option_prevision_id INT DEFAULT NULL, 
          preparation_prevision_id INT DEFAULT NULL, 
          third_option_prevision_id INT DEFAULT NULL, 
          candidate_prevision_id INT DEFAULT NULL, 
          national_prevision_id INT DEFAULT NULL, 
          population INT DEFAULT NULL, 
          priority VARCHAR(255) DEFAULT NULL, 
          risk BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1E449D110 ON election_city_card (first_candidate_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1B29FABBC ON election_city_card (headquarters_manager_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1E4A014FA ON election_city_card (politic_manager_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1781FEED9 ON election_city_card (task_force_manager_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1354DEDE5 ON election_city_card (candidate_option_prevision_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D15EC54712 ON election_city_card (preparation_prevision_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1F543170A ON election_city_card (third_option_prevision_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1EBF42685 ON election_city_card (candidate_prevision_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1B86B270B ON election_city_card (national_prevision_id)');
        $this->addSql('CREATE UNIQUE INDEX city_card_city_unique ON election_city_card (city_id)');
        $this->addSql('CREATE TABLE vote_result_list_collection (
          id SERIAL NOT NULL, 
          city_id INT DEFAULT NULL, 
          election_round_id INT DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_9C1DD9638BAC62AF ON vote_result_list_collection (city_id)');
        $this->addSql('CREATE INDEX IDX_9C1DD963FCBF5E32 ON vote_result_list_collection (election_round_id)');
        $this->addSql('CREATE TABLE ministry_list_total_result (
          id SERIAL NOT NULL, 
          ministry_vote_result_id INT DEFAULT NULL, 
          total INT DEFAULT 0 NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          nuance VARCHAR(255) DEFAULT NULL, 
          adherent_count INT DEFAULT NULL, 
          eligible_count INT DEFAULT NULL, 
          position INT DEFAULT NULL, 
          candidate_first_name VARCHAR(255) DEFAULT NULL, 
          candidate_last_name VARCHAR(255) DEFAULT NULL, 
          outgoing_mayor BOOLEAN DEFAULT \'false\' NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_99D1332580711B75 ON ministry_list_total_result (ministry_vote_result_id)');
        $this->addSql('CREATE TABLE election_city_partner (
          id SERIAL NOT NULL, 
          city_id INT NOT NULL, 
          label VARCHAR(255) NOT NULL, 
          consensus VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_704D77988BAC62AF ON election_city_partner (city_id)');
        $this->addSql('CREATE TABLE election_city_prevision (
          id SERIAL NOT NULL, 
          strategy VARCHAR(255) DEFAULT NULL, 
          name VARCHAR(255) DEFAULT NULL, 
          alliances VARCHAR(255) DEFAULT NULL, 
          allies VARCHAR(255) DEFAULT NULL, 
          validated_by VARCHAR(255) DEFAULT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE ministry_vote_result (
          id SERIAL NOT NULL, 
          election_round_id INT NOT NULL, 
          city_id INT DEFAULT NULL, 
          created_by_id INT DEFAULT NULL, 
          updated_by_id INT DEFAULT NULL, 
          registered INT NOT NULL, 
          abstentions INT NOT NULL, 
          participated INT NOT NULL, 
          expressed INT NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_B9F11DAEFCBF5E32 ON ministry_vote_result (election_round_id)');
        $this->addSql('CREATE INDEX IDX_B9F11DAE8BAC62AF ON ministry_vote_result (city_id)');
        $this->addSql('CREATE INDEX IDX_B9F11DAEB03A8386 ON ministry_vote_result (created_by_id)');
        $this->addSql('CREATE INDEX IDX_B9F11DAE896DBBDE ON ministry_vote_result (updated_by_id)');
        $this->addSql('CREATE UNIQUE INDEX ministry_vote_result_city_round_unique ON ministry_vote_result (city_id, election_round_id)');
        $this->addSql('CREATE TABLE list_total_result (
          id SERIAL NOT NULL, 
          list_id INT DEFAULT NULL, 
          vote_result_id INT NOT NULL, 
          total INT DEFAULT 0 NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A19B071E3DAE168B ON list_total_result (list_id)');
        $this->addSql('CREATE INDEX IDX_A19B071E45EB7186 ON list_total_result (vote_result_id)');
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
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55314F9525F06C53 ON elected_representatives_register (adherent_id)');
        $this->addSql('COMMENT ON COLUMN elected_representatives_register.adherent_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE devices (
          id SERIAL NOT NULL, 
          device_uuid VARCHAR(255) NOT NULL, 
          last_logged_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
          uuid UUID NOT NULL, 
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX devices_uuid_unique ON devices (uuid)');
        $this->addSql('CREATE UNIQUE INDEX devices_device_uuid_unique ON devices (device_uuid)');
        $this->addSql('COMMENT ON COLUMN devices.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE 
          donation_transactions 
        ADD 
          CONSTRAINT FK_89D6D36B4DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donators 
        ADD 
          CONSTRAINT FK_A902FDD725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donators 
        ADD 
          CONSTRAINT FK_A902FDD7DE59CB1A FOREIGN KEY (last_successful_donation_id) REFERENCES donations (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donators 
        ADD 
          CONSTRAINT FK_A902FDD7ABF665A8 FOREIGN KEY (reference_donation_id) REFERENCES donations (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donator_donator_tag 
        ADD 
          CONSTRAINT FK_6BAEC28C831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donator_donator_tag 
        ADD 
          CONSTRAINT FK_6BAEC28C71F026E6 FOREIGN KEY (donator_tag_id) REFERENCES donator_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA39BF75CAD FOREIGN KEY (
            legislative_candidate_managed_district_id
          ) REFERENCES districts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES referent_managed_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA37034326B FOREIGN KEY (
            coordinator_citizen_project_area_id
          ) REFERENCES coordinator_managed_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA31A912B27 FOREIGN KEY (coordinator_committee_area_id) REFERENCES coordinator_managed_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA339054338 FOREIGN KEY (procuration_managed_area_id) REFERENCES procuration_managed_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3E1B55931 FOREIGN KEY (assessor_managed_area_id) REFERENCES assessor_managed_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3E4A5D7A5 FOREIGN KEY (assessor_role_id) REFERENCES assessor_role_association (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA379DE69AA FOREIGN KEY (municipal_manager_role_id) REFERENCES municipal_manager_role_association (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA39801977F FOREIGN KEY (
            municipal_manager_supervisor_role_id
          ) REFERENCES municipal_manager_supervisor_role (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA394E3BB99 FOREIGN KEY (jecoute_managed_area_id) REFERENCES jecoute_managed_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3A132C3C5 FOREIGN KEY (managed_district_id) REFERENCES districts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3CC72679B FOREIGN KEY (
            municipal_chief_managed_area_id
          ) REFERENCES municipal_chief_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3FCCAF6D5 FOREIGN KEY (
            senatorial_candidate_managed_area_id
          ) REFERENCES senatorial_candidate_areas (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA379645AD5 FOREIGN KEY (lre_area_id) REFERENCES lre_area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA37657F304 FOREIGN KEY (candidate_managed_area_id) REFERENCES candidate_managed_area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA393494FA8 FOREIGN KEY (senator_area_id) REFERENCES senator_area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3122E5FF4 FOREIGN KEY (consular_managed_area_id) REFERENCES consular_managed_area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_subscription_type 
        ADD 
          CONSTRAINT FK_F93DC28A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_subscription_type 
        ADD 
          CONSTRAINT FK_F93DC28AB6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_adherent_tag 
        ADD 
          CONSTRAINT FK_DD297F8225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_adherent_tag 
        ADD 
          CONSTRAINT FK_DD297F82AED03543 FOREIGN KEY (adherent_tag_id) REFERENCES adherent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_thematic_community 
        ADD 
          CONSTRAINT FK_DAB0B4EC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_thematic_community 
        ADD 
          CONSTRAINT FK_DAB0B4EC1BE5825E FOREIGN KEY (thematic_community_id) REFERENCES thematic_community (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_referent_tag 
        ADD 
          CONSTRAINT FK_79E8AFFD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_referent_tag 
        ADD 
          CONSTRAINT FK_79E8AFFD9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_zone 
        ADD 
          CONSTRAINT FK_1C14D08525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_zone 
        ADD 
          CONSTRAINT FK_1C14D0859F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donations 
        ADD 
          CONSTRAINT FK_CDE98962831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donation_donation_tag 
        ADD 
          CONSTRAINT FK_F2D7087F4DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donation_donation_tag 
        ADD 
          CONSTRAINT FK_F2D7087F790547EA FOREIGN KEY (donation_tag_id) REFERENCES donation_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donator_kinship 
        ADD 
          CONSTRAINT FK_E542211D831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          donator_kinship 
        ADD 
          CONSTRAINT FK_E542211D4162C001 FOREIGN KEY (related_id) REFERENCES donators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          board_member 
        ADD 
          CONSTRAINT FK_DCFABEDF25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          board_member_roles 
        ADD 
          CONSTRAINT FK_1DD1E043C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          board_member_roles 
        ADD 
          CONSTRAINT FK_1DD1E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          saved_board_members 
        ADD 
          CONSTRAINT FK_32865A32FDCCD727 FOREIGN KEY (board_member_owner_id) REFERENCES board_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          saved_board_members 
        ADD 
          CONSTRAINT FK_32865A324821D202 FOREIGN KEY (board_member_saved_id) REFERENCES board_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          districts 
        ADD 
          CONSTRAINT FK_68E318DC80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          districts 
        ADD 
          CONSTRAINT FK_68E318DC9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_managed_areas_tags 
        ADD 
          CONSTRAINT FK_8BE84DD56B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES referent_managed_areas (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_managed_areas_tags 
        ADD 
          CONSTRAINT FK_8BE84DD59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C006717597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C0067135E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_team_member_committee 
        ADD 
          CONSTRAINT FK_EC89860BFE4CA267 FOREIGN KEY (referent_team_member_id) REFERENCES referent_team_member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_team_member_committee 
        ADD 
          CONSTRAINT FK_EC89860BED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          assessor_role_association 
        ADD 
          CONSTRAINT FK_B93395C2F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          municipal_manager_role_association_cities 
        ADD 
          CONSTRAINT FK_A713D9C2D96891C FOREIGN KEY (
            municipal_manager_role_association_id
          ) REFERENCES municipal_manager_role_association (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          municipal_manager_role_association_cities 
        ADD 
          CONSTRAINT FK_A713D9C28BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          municipal_manager_supervisor_role 
        ADD 
          CONSTRAINT FK_F304FF35E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_managed_areas 
        ADD 
          CONSTRAINT FK_DF8531749F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_membership 
        ADD 
          CONSTRAINT FK_2A99831625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_membership 
        ADD 
          CONSTRAINT FK_2A998316AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          political_committee_membership 
        ADD 
          CONSTRAINT FK_FD85437B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          political_committee_membership 
        ADD 
          CONSTRAINT FK_FD85437BC7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committees_memberships 
        ADD 
          CONSTRAINT FK_E7A6490E25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committees_memberships 
        ADD 
          CONSTRAINT FK_E7A6490EED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_memberships 
        ADD 
          CONSTRAINT FK_2E41816B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_memberships 
        ADD 
          CONSTRAINT FK_2E4181625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_feed_item 
        ADD 
          CONSTRAINT FK_4F1CDC80ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_feed_item 
        ADD 
          CONSTRAINT FK_4F1CDC80F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_feed_item 
        ADD 
          CONSTRAINT FK_4F1CDC8071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_feed_item_user_documents 
        ADD 
          CONSTRAINT FK_D269D0AABEF808A3 FOREIGN KEY (committee_feed_item_id) REFERENCES committee_feed_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_feed_item_user_documents 
        ADD 
          CONSTRAINT FK_D269D0AA6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_idea 
        ADD 
          CONSTRAINT FK_CA001C7212469DE2 FOREIGN KEY (category_id) REFERENCES ideas_workshop_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_idea 
        ADD 
          CONSTRAINT FK_CA001C72F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_idea 
        ADD 
          CONSTRAINT FK_CA001C72ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_ideas_themes 
        ADD 
          CONSTRAINT FK_DB4ED3145B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_ideas_themes 
        ADD 
          CONSTRAINT FK_DB4ED31459027487 FOREIGN KEY (theme_id) REFERENCES ideas_workshop_theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_ideas_needs 
        ADD 
          CONSTRAINT FK_75CEB995B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_ideas_needs 
        ADD 
          CONSTRAINT FK_75CEB99624AF264 FOREIGN KEY (need_id) REFERENCES ideas_workshop_need (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          senatorial_candidate_areas_tags 
        ADD 
          CONSTRAINT FK_F83208FAA7BF84E8 FOREIGN KEY (senatorial_candidate_area_id) REFERENCES senatorial_candidate_areas (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          senatorial_candidate_areas_tags 
        ADD 
          CONSTRAINT FK_F83208FA9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          lre_area 
        ADD 
          CONSTRAINT FK_8D3B8F189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          candidate_managed_area 
        ADD 
          CONSTRAINT FK_C604D2EA9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_charter 
        ADD 
          CONSTRAINT FK_D6F94F2B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          senator_area 
        ADD 
          CONSTRAINT FK_D229BBF7AEC89CE1 FOREIGN KEY (department_tag_id) REFERENCES referent_tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          consular_managed_area 
        ADD 
          CONSTRAINT FK_7937A51292CA96FD FOREIGN KEY (consular_district_id) REFERENCES consular_district (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          CONSTRAINT FK_6E7481A925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          CONSTRAINT FK_6E7481A92FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          CONSTRAINT FK_6E7481A96EA98020 FOREIGN KEY (found_duplicated_adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access 
        ADD 
          CONSTRAINT FK_421C13B98825BEFA FOREIGN KEY (delegator_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access 
        ADD 
          CONSTRAINT FK_421C13B9B7E7AE18 FOREIGN KEY (delegated_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          my_team_delegate_access_committee 
        ADD 
          CONSTRAINT FK_C52A163FFD98FA7A FOREIGN KEY (delegated_access_id) REFERENCES my_team_delegated_access (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          my_team_delegate_access_committee 
        ADD 
          CONSTRAINT FK_C52A163FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_commitment 
        ADD 
          CONSTRAINT FK_D239EF6F25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_mandate 
        ADD 
          CONSTRAINT FK_9C0C3D6025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_mandate 
        ADD 
          CONSTRAINT FK_9C0C3D60ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_mandate 
        ADD 
          CONSTRAINT FK_9C0C3D60AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_provisional_supervisor 
        ADD 
          CONSTRAINT FK_E394C3D425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_provisional_supervisor 
        ADD 
          CONSTRAINT FK_E394C3D4ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_tags 
        ADD 
          CONSTRAINT FK_135D29D99F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_zone 
        ADD 
          CONSTRAINT FK_A4CCEF0780E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_zone_parent 
        ADD 
          CONSTRAINT FK_8E49B9DDD62C21B FOREIGN KEY (child_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_zone_parent 
        ADD 
          CONSTRAINT FK_8E49B9D727ACA70 FOREIGN KEY (parent_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent 
        ADD 
          CONSTRAINT FK_FE9AAC6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_areas 
        ADD 
          CONSTRAINT FK_75CEBC6C35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_areas 
        ADD 
          CONSTRAINT FK_75CEBC6CBD0F409C FOREIGN KEY (area_id) REFERENCES referent_area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          CONSTRAINT FK_BC75A60A810B5A42 FOREIGN KEY (
            person_organizational_chart_item_id
          ) REFERENCES organizational_chart_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          CONSTRAINT FK_BC75A60A35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          CONSTRAINT FK_BC75A60A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_person_link_committee 
        ADD 
          CONSTRAINT FK_1C97B2A5B3E4DE86 FOREIGN KEY (referent_person_link_id) REFERENCES referent_person_link (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_person_link_committee 
        ADD 
          CONSTRAINT FK_1C97B2A5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_category_skills 
        ADD 
          CONSTRAINT FK_168C868A12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_category_skills 
        ADD 
          CONSTRAINT FK_168C868A5585C142 FOREIGN KEY (skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_managed_users_message 
        ADD 
          CONSTRAINT FK_1E41AC6125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          summaries 
        ADD 
          CONSTRAINT FK_66783CCA7597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          summary_mission_type_wishes 
        ADD 
          CONSTRAINT FK_7F3FC70F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          summary_mission_type_wishes 
        ADD 
          CONSTRAINT FK_7F3FC70F547018DE FOREIGN KEY (mission_type_id) REFERENCES member_summary_mission_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          summary_skills 
        ADD 
          CONSTRAINT FK_2FD2B63C2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          summary_skills 
        ADD 
          CONSTRAINT FK_2FD2B63C5585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          organizational_chart_item 
        ADD 
          CONSTRAINT FK_29C1CBACA977936C FOREIGN KEY (tree_root) REFERENCES organizational_chart_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          organizational_chart_item 
        ADD 
          CONSTRAINT FK_29C1CBAC727ACA70 FOREIGN KEY (parent_id) REFERENCES organizational_chart_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          events_invitations 
        ADD 
          CONSTRAINT FK_B94D5AAD71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC05FDA7B0BF FOREIGN KEY (community_id) REFERENCES thematic_community (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC0525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC05E7A1254A FOREIGN KEY (contact_id) REFERENCES thematic_community_contact (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership_user_list_definition 
        ADD 
          CONSTRAINT FK_58815EB9403AE2A5 FOREIGN KEY (
            thematic_community_membership_id
          ) REFERENCES thematic_community_membership (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership_user_list_definition 
        ADD 
          CONSTRAINT FK_58815EB9F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          events_registrations 
        ADD 
          CONSTRAINT FK_EEFA30C071F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          projection_managed_users_zone 
        ADD 
          CONSTRAINT FK_E4D4ADCDC679DD78 FOREIGN KEY (managed_user_id) REFERENCES projection_managed_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          projection_managed_users_zone 
        ADD 
          CONSTRAINT FK_E4D4ADCD9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_survey 
        ADD 
          CONSTRAINT FK_EC4948E5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_survey 
        ADD 
          CONSTRAINT FK_EC4948E59F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_survey 
        ADD 
          CONSTRAINT FK_EC4948E54B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_region 
        ADD 
          CONSTRAINT FK_4E74226F39192B5C FOREIGN KEY (geo_region_id) REFERENCES geo_region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_choice 
        ADD 
          CONSTRAINT FK_80BD898B1E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_suggested_question 
        ADD 
          CONSTRAINT FK_8280E9DABF396750 FOREIGN KEY (id) REFERENCES jecoute_question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          CONSTRAINT FK_34362099F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          CONSTRAINT FK_3436209B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_answer 
        ADD 
          CONSTRAINT FK_12FB393EA6DF29BA FOREIGN KEY (survey_question_id) REFERENCES jecoute_survey_question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_answer 
        ADD 
          CONSTRAINT FK_12FB393E3C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_answer_selected_choices 
        ADD 
          CONSTRAINT FK_10DF117259C0831 FOREIGN KEY (data_answer_id) REFERENCES jecoute_data_answer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_answer_selected_choices 
        ADD 
          CONSTRAINT FK_10DF117998666D1 FOREIGN KEY (choice_id) REFERENCES jecoute_choice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_survey_question 
        ADD 
          CONSTRAINT FK_A2FBFA81B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_survey_question 
        ADD 
          CONSTRAINT FK_A2FBFA811E27F6BF FOREIGN KEY (question_id) REFERENCES jecoute_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_survey 
        ADD 
          CONSTRAINT FK_6579E8E7F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_survey 
        ADD 
          CONSTRAINT FK_6579E8E794A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          jecoute_data_survey 
        ADD 
          CONSTRAINT FK_6579E8E7B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_rounds 
        ADD 
          CONSTRAINT FK_37C02EA0A708DAFF FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          social_shares 
        ADD 
          CONSTRAINT FK_8E1413A085040FAD FOREIGN KEY (social_share_category_id) REFERENCES social_share_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          social_shares 
        ADD 
          CONSTRAINT FK_8E1413A0EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          formation_files 
        ADD 
          CONSTRAINT FK_70BEDE2CAFC2B591 FOREIGN KEY (module_id) REFERENCES formation_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          formation_modules 
        ADD 
          CONSTRAINT FK_6B4806AC2E30CD41 FOREIGN KEY (axe_id) REFERENCES formation_axes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          formation_modules 
        ADD 
          CONSTRAINT FK_6B4806ACEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          formation_axes 
        ADD 
          CONSTRAINT FK_7E652CB6D96C566B FOREIGN KEY (path_id) REFERENCES formation_paths (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          formation_axes 
        ADD 
          CONSTRAINT FK_7E652CB6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_projects 
        ADD 
          CONSTRAINT FK_651490212469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_projects 
        ADD 
          CONSTRAINT FK_6514902B5315DF4 FOREIGN KEY (turnkey_project_id) REFERENCES turnkey_projects (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_projects_skills 
        ADD 
          CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_projects_skills 
        ADD 
          CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_referent_tag 
        ADD 
          CONSTRAINT FK_73ED204AB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_referent_tag 
        ADD 
          CONSTRAINT FK_73ED204A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_region 
        ADD 
          CONSTRAINT FK_A4B3C808F92F3E70 FOREIGN KEY (country_id) REFERENCES geo_country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_region 
        ADD 
          CONSTRAINT FK_A4B3C80880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_consular_district 
        ADD 
          CONSTRAINT FK_BBFC552F72D24D35 FOREIGN KEY (foreign_district_id) REFERENCES geo_foreign_district (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_consular_district 
        ADD 
          CONSTRAINT FK_BBFC552F80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_canton 
        ADD 
          CONSTRAINT FK_F04FC05FAE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_canton 
        ADD 
          CONSTRAINT FK_F04FC05F80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_district 
        ADD 
          CONSTRAINT FK_DF782326AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_district 
        ADD 
          CONSTRAINT FK_DF78232680E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_borough 
        ADD 
          CONSTRAINT FK_144958748BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_borough 
        ADD 
          CONSTRAINT FK_1449587480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_country 
        ADD 
          CONSTRAINT FK_E465446472D24D35 FOREIGN KEY (foreign_district_id) REFERENCES geo_foreign_district (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_country 
        ADD 
          CONSTRAINT FK_E465446480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_custom_zone 
        ADD 
          CONSTRAINT FK_ABE4DB5A80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_foreign_district 
        ADD 
          CONSTRAINT FK_973BE1F198755666 FOREIGN KEY (custom_zone_id) REFERENCES geo_custom_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_foreign_district 
        ADD 
          CONSTRAINT FK_973BE1F180E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city 
        ADD 
          CONSTRAINT FK_297C2D34AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city 
        ADD 
          CONSTRAINT FK_297C2D346D3B1930 FOREIGN KEY (city_community_id) REFERENCES geo_city_community (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city 
        ADD 
          CONSTRAINT FK_297C2D349D25CF90 FOREIGN KEY (replacement_id) REFERENCES geo_city (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city 
        ADD 
          CONSTRAINT FK_297C2D3480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_district 
        ADD 
          CONSTRAINT FK_5C4191F8BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_district 
        ADD 
          CONSTRAINT FK_5C4191FB08FA272 FOREIGN KEY (district_id) REFERENCES geo_district (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_canton 
        ADD 
          CONSTRAINT FK_A4AB64718BAC62AF FOREIGN KEY (city_id) REFERENCES geo_city (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_canton 
        ADD 
          CONSTRAINT FK_A4AB64718D070D0B FOREIGN KEY (canton_id) REFERENCES geo_canton (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_department 
        ADD 
          CONSTRAINT FK_B460660498260155 FOREIGN KEY (region_id) REFERENCES geo_region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_department 
        ADD 
          CONSTRAINT FK_B460660480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_community 
        ADD 
          CONSTRAINT FK_E5805E0880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_community_department 
        ADD 
          CONSTRAINT FK_1E2D6D066D3B1930 FOREIGN KEY (city_community_id) REFERENCES geo_city_community (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          geo_city_community_department 
        ADD 
          CONSTRAINT FK_1E2D6D06AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_elements 
        ADD 
          CONSTRAINT FK_691284C5579F4768 FOREIGN KEY (chapter_id) REFERENCES mooc_chapter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_elements 
        ADD 
          CONSTRAINT FK_691284C53DA5256D FOREIGN KEY (image_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_element_attachment_link 
        ADD 
          CONSTRAINT FK_324635C7B1828C9D FOREIGN KEY (base_mooc_element_id) REFERENCES mooc_elements (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_element_attachment_link 
        ADD 
          CONSTRAINT FK_324635C7653157F7 FOREIGN KEY (attachment_link_id) REFERENCES mooc_attachment_link (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_element_attachment_file 
        ADD 
          CONSTRAINT FK_88759A26B1828C9D FOREIGN KEY (base_mooc_element_id) REFERENCES mooc_elements (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_element_attachment_file 
        ADD 
          CONSTRAINT FK_88759A265B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES mooc_attachment_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc 
        ADD 
          CONSTRAINT FK_9D5D3B55684DD106 FOREIGN KEY (article_image_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc 
        ADD 
          CONSTRAINT FK_9D5D3B5543C8160D FOREIGN KEY (list_image_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mooc_chapter 
        ADD 
          CONSTRAINT FK_A3EDA0D1255EEB87 FOREIGN KEY (mooc_id) REFERENCES mooc (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          deputy_managed_users_message 
        ADD 
          CONSTRAINT FK_5AC419DDB08FA272 FOREIGN KEY (district_id) REFERENCES districts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          deputy_managed_users_message 
        ADD 
          CONSTRAINT FK_5AC419DD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          assessor_requests 
        ADD 
          CONSTRAINT FK_26BC800F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          assessor_requests_vote_place_wishes 
        ADD 
          CONSTRAINT FK_1517FC131BD1903D FOREIGN KEY (assessor_request_id) REFERENCES assessor_requests (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          assessor_requests_vote_place_wishes 
        ADD 
          CONSTRAINT FK_1517FC13F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_thread 
        ADD 
          CONSTRAINT FK_CE975BDDAA334807 FOREIGN KEY (answer_id) REFERENCES ideas_workshop_answer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_thread 
        ADD 
          CONSTRAINT FK_CE975BDDF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_question 
        ADD 
          CONSTRAINT FK_111C43E4CC0B46A8 FOREIGN KEY (guideline_id) REFERENCES ideas_workshop_guideline (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_comment 
        ADD 
          CONSTRAINT FK_18589988E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_comment 
        ADD 
          CONSTRAINT FK_18589988F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_vote 
        ADD 
          CONSTRAINT FK_9A9B53535B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_vote 
        ADD 
          CONSTRAINT FK_9A9B5353F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_answer 
        ADD 
          CONSTRAINT FK_256A5D7B1E27F6BF FOREIGN KEY (question_id) REFERENCES ideas_workshop_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_answer 
        ADD 
          CONSTRAINT FK_256A5D7B5B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_answer_user_documents 
        ADD 
          CONSTRAINT FK_824E75E79C97E9FB FOREIGN KEY (ideas_workshop_answer_id) REFERENCES ideas_workshop_answer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ideas_workshop_answer_user_documents 
        ADD 
          CONSTRAINT FK_824E75E76A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          home_blocks 
        ADD 
          CONSTRAINT FK_3EE9FCC5EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          interactive_invitation_has_choices 
        ADD 
          CONSTRAINT FK_31A811A2A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES interactive_invitations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          interactive_invitation_has_choices 
        ADD 
          CONSTRAINT FK_31A811A2998666D1 FOREIGN KEY (choice_id) REFERENCES interactive_choices (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          legislative_candidates 
        ADD 
          CONSTRAINT FK_AE55AF9B23F5C396 FOREIGN KEY (district_zone_id) REFERENCES legislative_district_zones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          legislative_candidates 
        ADD 
          CONSTRAINT FK_AE55AF9BEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          events_categories 
        ADD 
          CONSTRAINT FK_EF0AF3E9A267D842 FOREIGN KEY (event_group_category_id) REFERENCES event_group_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          designation_referent_tag 
        ADD 
          CONSTRAINT FK_7538F35AFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          designation_referent_tag 
        ADD 
          CONSTRAINT FK_7538F35A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round 
        ADD 
          CONSTRAINT FK_F15D87B7A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_election_pool 
        ADD 
          CONSTRAINT FK_E6665F19FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_election_pool 
        ADD 
          CONSTRAINT FK_E6665F19C1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate 
        ADD 
          CONSTRAINT FK_3F426D6D5F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate 
        ADD 
          CONSTRAINT FK_3F426D6D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election 
        ADD 
          CONSTRAINT FK_4E144C94FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_result 
        ADD 
          CONSTRAINT FK_62C86890FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_voters_list 
        ADD 
          CONSTRAINT FK_3C73500DA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_voters_list_voter 
        ADD 
          CONSTRAINT FK_7CC26956FB0C8C84 FOREIGN KEY (voters_list_id) REFERENCES voting_platform_voters_list (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_voters_list_voter 
        ADD 
          CONSTRAINT FK_7CC26956EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voting_platform_voter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          CONSTRAINT FK_DCBB2B7BEBB4B8AD FOREIGN KEY (voter_id) REFERENCES voting_platform_voter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote 
        ADD 
          CONSTRAINT FK_DCBB2B7BFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group 
        ADD 
          CONSTRAINT FK_2C1A353AC1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_voter 
        ADD 
          CONSTRAINT FK_AB02EC0225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool 
        ADD 
          CONSTRAINT FK_7225D6EFA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_result 
        ADD 
          CONSTRAINT FK_67EFA0E4A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group_result 
        ADD 
          CONSTRAINT FK_7249D5375F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_candidate_group_result 
        ADD 
          CONSTRAINT FK_7249D537B5BA5CC5 FOREIGN KEY (election_pool_result_id) REFERENCES voting_platform_election_pool_result (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool_result 
        ADD 
          CONSTRAINT FK_13C1C73FC1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_pool_result 
        ADD 
          CONSTRAINT FK_13C1C73F8FFC0F0B FOREIGN KEY (election_round_result_id) REFERENCES voting_platform_election_round_result (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_result 
        ADD 
          CONSTRAINT FK_F2670966FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES voting_platform_election_round (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_round_result 
        ADD 
          CONSTRAINT FK_F267096619FCFB29 FOREIGN KEY (election_result_id) REFERENCES voting_platform_election_result (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          CONSTRAINT FK_7AAD259FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          CONSTRAINT FK_7AAD259FAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_election_entity 
        ADD 
          CONSTRAINT FK_7AAD259FA708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_choice 
        ADD 
          CONSTRAINT FK_B009F31145EB7186 FOREIGN KEY (vote_result_id) REFERENCES voting_platform_vote_result (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_choice 
        ADD 
          CONSTRAINT FK_B009F3115F0A9B94 FOREIGN KEY (candidate_group_id) REFERENCES voting_platform_candidate_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          voting_platform_vote_choice 
        ADD 
          CONSTRAINT FK_B009F311C1E98F21 FOREIGN KEY (election_pool_id) REFERENCES voting_platform_election_pool (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          CONSTRAINT FK_5387574A876C4DDA FOREIGN KEY (organizer_id) REFERENCES adherents (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          CONSTRAINT FK_5387574AED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          CONSTRAINT FK_5387574AB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          event_referent_tag 
        ADD 
          CONSTRAINT FK_D3C8F5BE71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          event_referent_tag 
        ADD 
          CONSTRAINT FK_D3C8F5BE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          event_zone 
        ADD 
          CONSTRAINT FK_BF208CAC3B1C4B73 FOREIGN KEY (base_event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          event_zone 
        ADD 
          CONSTRAINT FK_BF208CAC9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          event_user_documents 
        ADD 
          CONSTRAINT FK_7D14491F71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          event_user_documents 
        ADD 
          CONSTRAINT FK_7D14491F6A24B1A2 FOREIGN KEY (user_document_id) REFERENCES user_documents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_election 
        ADD 
          CONSTRAINT FK_2CA406E5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_election 
        ADD 
          CONSTRAINT FK_2CA406E5FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          filesystem_file 
        ADD 
          CONSTRAINT FK_47F0AE28B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          filesystem_file 
        ADD 
          CONSTRAINT FK_47F0AE28896DBBDE FOREIGN KEY (updated_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          filesystem_file 
        ADD 
          CONSTRAINT FK_47F0AE28727ACA70 FOREIGN KEY (parent_id) REFERENCES filesystem_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          filesystem_file_permission 
        ADD 
          CONSTRAINT FK_BD623E4C93CB796C FOREIGN KEY (file_id) REFERENCES filesystem_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_measures 
        ADD 
          CONSTRAINT FK_BA475ED737E924 FOREIGN KEY (manifesto_id) REFERENCES timeline_manifestos (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_measures_profiles 
        ADD 
          CONSTRAINT FK_B83D81AE5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_measures_profiles 
        ADD 
          CONSTRAINT FK_B83D81AECCFA12B8 FOREIGN KEY (profile_id) REFERENCES timeline_profiles (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_themes_measures 
        ADD 
          CONSTRAINT FK_EB8A7B0C5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_themes_measures 
        ADD 
          CONSTRAINT FK_EB8A7B0C59027487 FOREIGN KEY (theme_id) REFERENCES timeline_themes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_theme_translations 
        ADD 
          CONSTRAINT FK_F81F72932C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_themes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_measure_translations 
        ADD 
          CONSTRAINT FK_5C9EB6072C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_measures (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_manifestos 
        ADD 
          CONSTRAINT FK_C6ED4403EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_manifesto_translations 
        ADD 
          CONSTRAINT FK_F7BD6C172C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_manifestos (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_themes 
        ADD 
          CONSTRAINT FK_8ADDB8F6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          timeline_profile_translations 
        ADD 
          CONSTRAINT FK_41B3A6DA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES timeline_profiles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A044544E891720 FOREIGN KEY (committee_election_id) REFERENCES committee_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A04454FCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A04454A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES committee_candidacy_invitation (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A044548D4924C4 FOREIGN KEY (binome_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA745F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA745B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA745A2DD3412 FOREIGN KEY (citizen_action_id) REFERENCES events (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA745ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA74583B12DAC FOREIGN KEY (community_event_id) REFERENCES events (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA7455B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA745E2904019 FOREIGN KEY (thread_id) REFERENCES ideas_workshop_thread (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          reports 
        ADD 
          CONSTRAINT FK_F11FA7453A31E89B FOREIGN KEY (thread_comment_id) REFERENCES ideas_workshop_comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_segment 
        ADD 
          CONSTRAINT FK_9DF0C7EBF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_messages 
        ADD 
          CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_messages 
        ADD 
          CONSTRAINT FK_D187C183D395B25E FOREIGN KEY (filter_id) REFERENCES adherent_message_filters (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign 
        ADD 
          CONSTRAINT FK_CFABD3094BD2A4C0 FOREIGN KEY (report_id) REFERENCES mailchimp_campaign_report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign 
        ADD 
          CONSTRAINT FK_CFABD309537A1329 FOREIGN KEY (message_id) REFERENCES adherent_messages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign_mailchimp_segment 
        ADD 
          CONSTRAINT FK_901CE107828112CC FOREIGN KEY (mailchimp_campaign_id) REFERENCES mailchimp_campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign_mailchimp_segment 
        ADD 
          CONSTRAINT FK_901CE107D21E482E FOREIGN KEY (mailchimp_segment_id) REFERENCES mailchimp_segment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94FAF04979 FOREIGN KEY (adherent_segment_id) REFERENCES adherent_segment (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F949C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F949F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_user_filter_referent_tag 
        ADD 
          CONSTRAINT FK_F2BB20FEEFAB50C4 FOREIGN KEY (referent_user_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_user_filter_referent_tag 
        ADD 
          CONSTRAINT FK_F2BB20FE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          pages 
        ADD 
          CONSTRAINT FK_2074E5755B42DC0F FOREIGN KEY (header_media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          pages 
        ADD 
          CONSTRAINT FK_2074E575EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          procuration_proxies_to_election_rounds 
        ADD 
          CONSTRAINT FK_D075F5A9E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          procuration_proxies_to_election_rounds 
        ADD 
          CONSTRAINT FK_D075F5A9FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          administrator_export_history 
        ADD 
          CONSTRAINT FK_10499F014B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_certification_histories 
        ADD 
          CONSTRAINT FK_732EE81A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_certification_histories 
        ADD 
          CONSTRAINT FK_732EE81A4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBC3BF0CCB3 FOREIGN KEY (source_committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBC5C34CBC4 FOREIGN KEY (destination_committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBC50FA8329 FOREIGN KEY (merged_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBCA8E1562 FOREIGN KEY (reverted_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories_merged_memberships 
        ADD 
          CONSTRAINT FK_CB8E336F9379ED92 FOREIGN KEY (committee_merge_history_id) REFERENCES committee_merge_histories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories_merged_memberships 
        ADD 
          CONSTRAINT FK_CB8E336FFCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_email_subscription_histories 
        ADD 
          CONSTRAINT FK_51AD8354B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_email_subscription_history_referent_tag 
        ADD 
          CONSTRAINT FK_6FFBE6E88FCB8132 FOREIGN KEY (email_subscription_history_id) REFERENCES adherent_email_subscription_histories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          adherent_email_subscription_history_referent_tag 
        ADD 
          CONSTRAINT FK_6FFBE6E89C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committees_membership_histories 
        ADD 
          CONSTRAINT FK_4BBAE2C7ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_membership_history_referent_tag 
        ADD 
          CONSTRAINT FK_B6A8C718123C64CE FOREIGN KEY (
            committee_membership_history_id
          ) REFERENCES committees_membership_histories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_membership_history_referent_tag 
        ADD 
          CONSTRAINT FK_B6A8C7189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          user_authorizations 
        ADD 
          CONSTRAINT FK_40448230A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          user_authorizations 
        ADD 
          CONSTRAINT FK_4044823019EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_refresh_tokens 
        ADD 
          CONSTRAINT FK_5AB6872CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_access_tokens 
        ADD 
          CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_access_tokens 
        ADD 
          CONSTRAINT FK_CA42527CA76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_access_tokens 
        ADD 
          CONSTRAINT FK_CA42527C94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_auth_codes 
        ADD 
          CONSTRAINT FK_BB493F8319EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_auth_codes 
        ADD 
          CONSTRAINT FK_BB493F83A76ED395 FOREIGN KEY (user_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          oauth_auth_codes 
        ADD 
          CONSTRAINT FK_BB493F8394A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          referent_space_access_information 
        ADD 
          CONSTRAINT FK_CD8FDF4825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_convocation 
        ADD 
          CONSTRAINT FK_A9919BF0AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_convocation 
        ADD 
          CONSTRAINT FK_A9919BF0C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_convocation 
        ADD 
          CONSTRAINT FK_A9919BF0B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          political_committee_feed_item 
        ADD 
          CONSTRAINT FK_54369E83C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          political_committee_feed_item 
        ADD 
          CONSTRAINT FK_54369E83F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_official_report 
        ADD 
          CONSTRAINT FK_8D80D385C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_official_report 
        ADD 
          CONSTRAINT FK_8D80D385F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_official_report 
        ADD 
          CONSTRAINT FK_8D80D385B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_official_report 
        ADD 
          CONSTRAINT FK_8D80D385896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_membership_log 
        ADD 
          CONSTRAINT FK_2F6D242025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          CONSTRAINT FK_14CBC36BAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          CONSTRAINT FK_14CBC36B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          CONSTRAINT FK_14CBC36BFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          political_committee 
        ADD 
          CONSTRAINT FK_39FAEE95AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_feed_item 
        ADD 
          CONSTRAINT FK_45241D62AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_feed_item 
        ADD 
          CONSTRAINT FK_45241D62F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_choice 
        ADD 
          CONSTRAINT FK_63EBCF6B8649F5F1 FOREIGN KEY (election_poll_id) REFERENCES territorial_council_election_poll (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote 
        ADD 
          CONSTRAINT FK_BCDA0C15998666D1 FOREIGN KEY (choice_id) REFERENCES territorial_council_election_poll_choice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote 
        ADD 
          CONSTRAINT FK_BCDA0C151FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_official_report_document 
        ADD 
          CONSTRAINT FK_78C1161DB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_official_report_document 
        ADD 
          CONSTRAINT FK_78C1161D4BD2A4C0 FOREIGN KEY (report_id) REFERENCES territorial_council_official_report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council 
        ADD 
          CONSTRAINT FK_B6DCA2A5B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_referent_tag 
        ADD 
          CONSTRAINT FK_78DBEB90AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_referent_tag 
        ADD 
          CONSTRAINT FK_78DBEB909C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_zone 
        ADD 
          CONSTRAINT FK_9467B41EAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_zone 
        ADD 
          CONSTRAINT FK_9467B41E9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          political_committee_quality 
        ADD 
          CONSTRAINT FK_243D6D3A78632915 FOREIGN KEY (
            political_committee_membership_id
          ) REFERENCES political_committee_membership (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_quality 
        ADD 
          CONSTRAINT FK_C018E022E797FAB0 FOREIGN KEY (
            territorial_council_membership_id
          ) REFERENCES territorial_council_membership (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy_invitation 
        ADD 
          CONSTRAINT FK_DA86009A1FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A708DAFF FOREIGN KEY (election_id) REFERENCES territorial_council_election (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B61FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES territorial_council_candidacy_invitation (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B68D4924C4 FOREIGN KEY (binome_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_zone 
        ADD 
          CONSTRAINT FK_C52FC4A712469DE2 FOREIGN KEY (category_id) REFERENCES elected_representative_zone_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_zone_referent_tag 
        ADD 
          CONSTRAINT FK_D2B7A8C5BE31A103 FOREIGN KEY (elected_representative_zone_id) REFERENCES elected_representative_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_zone_referent_tag 
        ADD 
          CONSTRAINT FK_D2B7A8C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_zone_parent 
        ADD 
          CONSTRAINT FK_CECA906FDD62C21B FOREIGN KEY (child_id) REFERENCES elected_representative_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_zone_parent 
        ADD 
          CONSTRAINT FK_CECA906F727ACA70 FOREIGN KEY (parent_id) REFERENCES elected_representative_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_political_function 
        ADD 
          CONSTRAINT FK_303BAF41D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_political_function 
        ADD 
          CONSTRAINT FK_303BAF416C1129CD FOREIGN KEY (mandate_id) REFERENCES elected_representative_mandate (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_social_network_link 
        ADD 
          CONSTRAINT FK_231377B5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_mandate 
        ADD 
          CONSTRAINT FK_386091469F2C3FAB FOREIGN KEY (zone_id) REFERENCES elected_representative_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_mandate 
        ADD 
          CONSTRAINT FK_38609146283AB2A9 FOREIGN KEY (geo_zone_id) REFERENCES geo_zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_mandate 
        ADD 
          CONSTRAINT FK_38609146D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF7566D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF7566F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF756625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF75664B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_sponsorship 
        ADD 
          CONSTRAINT FK_CA6D486D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_label 
        ADD 
          CONSTRAINT FK_D8143704D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative 
        ADD 
          CONSTRAINT FK_BF51F0FD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition 
        ADD 
          CONSTRAINT FK_A9C53A24D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition 
        ADD 
          CONSTRAINT FK_A9C53A24F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          clarifications 
        ADD 
          CONSTRAINT FK_2FAB8972EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ton_macron_friend_invitation_has_choices 
        ADD 
          CONSTRAINT FK_BB3BCAEEA35D7AF0 FOREIGN KEY (invitation_id) REFERENCES ton_macron_friend_invitations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ton_macron_friend_invitation_has_choices 
        ADD 
          CONSTRAINT FK_BB3BCAEE998666D1 FOREIGN KEY (choice_id) REFERENCES ton_macron_choices (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committees 
        ADD 
          CONSTRAINT FK_A36198C6B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_referent_tag 
        ADD 
          CONSTRAINT FK_285EB1C5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_referent_tag 
        ADD 
          CONSTRAINT FK_285EB1C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_zone 
        ADD 
          CONSTRAINT FK_37C5F224ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_zone 
        ADD 
          CONSTRAINT FK_37C5F2249F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          custom_search_results 
        ADD 
          CONSTRAINT FK_38973E54EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          turnkey_projects 
        ADD 
          CONSTRAINT FK_CB66CFAE12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          turnkey_project_turnkey_project_file 
        ADD 
          CONSTRAINT FK_67BF8377B5315DF4 FOREIGN KEY (turnkey_project_id) REFERENCES turnkey_projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          turnkey_project_turnkey_project_file 
        ADD 
          CONSTRAINT FK_67BF83777D06E1CD FOREIGN KEY (turnkey_project_file_id) REFERENCES turnkey_projects_files (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          application_request_volunteer 
        ADD 
          CONSTRAINT FK_1139657025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_technical_skill 
        ADD 
          CONSTRAINT FK_7F8C5C1EB8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_technical_skill 
        ADD 
          CONSTRAINT FK_7F8C5C1EE98F0EFD FOREIGN KEY (technical_skill_id) REFERENCES application_request_technical_skill (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_theme 
        ADD 
          CONSTRAINT FK_5427AF53B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_theme 
        ADD 
          CONSTRAINT FK_5427AF5359027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_application_request_tag 
        ADD 
          CONSTRAINT FK_6F3FA269B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_application_request_tag 
        ADD 
          CONSTRAINT FK_6F3FA2699644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_referent_tag 
        ADD 
          CONSTRAINT FK_DA291742B8D6887 FOREIGN KEY (volunteer_request_id) REFERENCES application_request_volunteer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          volunteer_request_referent_tag 
        ADD 
          CONSTRAINT FK_DA2917429C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          application_request_running_mate 
        ADD 
          CONSTRAINT FK_D1D6095625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          running_mate_request_theme 
        ADD 
          CONSTRAINT FK_A7326227CEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          running_mate_request_theme 
        ADD 
          CONSTRAINT FK_A732622759027487 FOREIGN KEY (theme_id) REFERENCES application_request_theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          running_mate_request_application_request_tag 
        ADD 
          CONSTRAINT FK_9D534FCFCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          running_mate_request_application_request_tag 
        ADD 
          CONSTRAINT FK_9D534FCF9644FEDA FOREIGN KEY (application_request_tag_id) REFERENCES application_request_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          running_mate_request_referent_tag 
        ADD 
          CONSTRAINT FK_53AB4FABCEDF4387 FOREIGN KEY (running_mate_request_id) REFERENCES application_request_running_mate (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          running_mate_request_referent_tag 
        ADD 
          CONSTRAINT FK_53AB4FAB9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          committee_candidacy_invitation 
        ADD 
          CONSTRAINT FK_368B01611FB354CD FOREIGN KEY (membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_committee_supports 
        ADD 
          CONSTRAINT FK_F694C3BCB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          citizen_project_committee_supports 
        ADD 
          CONSTRAINT FK_F694C3BCED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          chez_vous_measures 
        ADD 
          CONSTRAINT FK_E6E8973E8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          chez_vous_measures 
        ADD 
          CONSTRAINT FK_E6E8973EC54C8C93 FOREIGN KEY (type_id) REFERENCES chez_vous_measure_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          chez_vous_markers 
        ADD 
          CONSTRAINT FK_452F890F8BAC62AF FOREIGN KEY (city_id) REFERENCES chez_vous_cities (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          chez_vous_cities 
        ADD 
          CONSTRAINT FK_A42D9BEDAE80F5DF FOREIGN KEY (department_id) REFERENCES chez_vous_departments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          chez_vous_departments 
        ADD 
          CONSTRAINT FK_29E7DD5798260155 FOREIGN KEY (region_id) REFERENCES chez_vous_regions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          procuration_requests 
        ADD 
          CONSTRAINT FK_9769FD842F1B6663 FOREIGN KEY (found_proxy_id) REFERENCES procuration_proxies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          procuration_requests 
        ADD 
          CONSTRAINT FK_9769FD84888FDEEE FOREIGN KEY (
            procuration_request_found_by_id
          ) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          procuration_requests_to_election_rounds 
        ADD 
          CONSTRAINT FK_A47BBD53128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          procuration_requests_to_election_rounds 
        ADD 
          CONSTRAINT FK_A47BBD53FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        ADD 
          CONSTRAINT FK_213A5F1EF0ED738A FOREIGN KEY (sub_approach_id) REFERENCES programmatic_foundation_sub_approach (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure_tag 
        ADD 
          CONSTRAINT FK_F004297F5DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure_tag 
        ADD 
          CONSTRAINT FK_F004297FBAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project 
        ADD 
          CONSTRAINT FK_8E8E96D55DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project_tag 
        ADD 
          CONSTRAINT FK_9F63872166D1F9C FOREIGN KEY (project_id) REFERENCES programmatic_foundation_project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project_tag 
        ADD 
          CONSTRAINT FK_9F63872BAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_sub_approach 
        ADD 
          CONSTRAINT FK_735C1D0115140614 FOREIGN KEY (approach_id) REFERENCES programmatic_foundation_approach (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          web_hooks 
        ADD 
          CONSTRAINT FK_CDB836AD19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          proposals 
        ADD 
          CONSTRAINT FK_A5BA3A8FEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          proposal_proposal_theme 
        ADD 
          CONSTRAINT FK_6B80CE41F4792058 FOREIGN KEY (proposal_id) REFERENCES proposals (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          proposal_proposal_theme 
        ADD 
          CONSTRAINT FK_6B80CE41B85948AF FOREIGN KEY (proposal_theme_id) REFERENCES proposals_themes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          order_articles 
        ADD 
          CONSTRAINT FK_5E25D3D9EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          order_section_order_article 
        ADD 
          CONSTRAINT FK_A956D4E4C14E7BC9 FOREIGN KEY (order_article_id) REFERENCES order_articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          order_section_order_article 
        ADD 
          CONSTRAINT FK_A956D4E46BF91E2F FOREIGN KEY (order_section_id) REFERENCES order_sections (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          articles 
        ADD 
          CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES articles_categories (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          articles 
        ADD 
          CONSTRAINT FK_BFDD3168EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          article_proposal_theme 
        ADD 
          CONSTRAINT FK_F6B9A2217294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          article_proposal_theme 
        ADD 
          CONSTRAINT FK_F6B9A221B85948AF FOREIGN KEY (proposal_theme_id) REFERENCES proposals_themes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          unregistrations 
        ADD 
          CONSTRAINT FK_F9E4AA0C5B30B80B FOREIGN KEY (excluded_by_id) REFERENCES administrators (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          unregistration_referent_tag 
        ADD 
          CONSTRAINT FK_59B7AC414D824CA FOREIGN KEY (unregistration_id) REFERENCES unregistrations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          unregistration_referent_tag 
        ADD 
          CONSTRAINT FK_59B7AC49C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          republican_silence_referent_tag 
        ADD 
          CONSTRAINT FK_543DED2612359909 FOREIGN KEY (republican_silence_id) REFERENCES republican_silence (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          republican_silence_referent_tag 
        ADD 
          CONSTRAINT FK_543DED269C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          cities 
        ADD 
          CONSTRAINT FK_D95DB16BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          department 
        ADD 
          CONSTRAINT FK_CD1DE18A98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          member_summary_job_experiences 
        ADD 
          CONSTRAINT FK_72DD8B7F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          member_summary_trainings 
        ADD 
          CONSTRAINT FK_C101987B2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          member_summary_languages 
        ADD 
          CONSTRAINT FK_70C88322AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB3498BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result_list 
        ADD 
          CONSTRAINT FK_677ED502DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_contact 
        ADD 
          CONSTRAINT FK_D04AFB68BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D18BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1E449D110 FOREIGN KEY (first_candidate_id) REFERENCES election_city_candidate (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1B29FABBC FOREIGN KEY (headquarters_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1E4A014FA FOREIGN KEY (politic_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1781FEED9 FOREIGN KEY (task_force_manager_id) REFERENCES election_city_manager (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1354DEDE5 FOREIGN KEY (candidate_option_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D15EC54712 FOREIGN KEY (preparation_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1F543170A FOREIGN KEY (third_option_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1EBF42685 FOREIGN KEY (candidate_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1B86B270B FOREIGN KEY (national_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection 
        ADD 
          CONSTRAINT FK_9C1DD9638BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection 
        ADD 
          CONSTRAINT FK_9C1DD963FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ministry_list_total_result 
        ADD 
          CONSTRAINT FK_99D1332580711B75 FOREIGN KEY (ministry_vote_result_id) REFERENCES ministry_vote_result (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          election_city_partner 
        ADD 
          CONSTRAINT FK_704D77988BAC62AF FOREIGN KEY (city_id) REFERENCES election_city_card (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAEFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAE8BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAEB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          list_total_result 
        ADD 
          CONSTRAINT FK_A19B071E3DAE168B FOREIGN KEY (list_id) REFERENCES vote_result_list (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          list_total_result 
        ADD 
          CONSTRAINT FK_A19B071E45EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE 
          elected_representatives_register 
        ADD 
          CONSTRAINT FK_55314F9525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donator_donator_tag DROP CONSTRAINT FK_6BAEC28C831BACAF');
        $this->addSql('ALTER TABLE donations DROP CONSTRAINT FK_CDE98962831BACAF');
        $this->addSql('ALTER TABLE donator_kinship DROP CONSTRAINT FK_E542211D831BACAF');
        $this->addSql('ALTER TABLE donator_kinship DROP CONSTRAINT FK_E542211D4162C001');
        $this->addSql('ALTER TABLE donators DROP CONSTRAINT FK_A902FDD725F06C53');
        $this->addSql('ALTER TABLE adherent_subscription_type DROP CONSTRAINT FK_F93DC28A25F06C53');
        $this->addSql('ALTER TABLE adherent_adherent_tag DROP CONSTRAINT FK_DD297F8225F06C53');
        $this->addSql('ALTER TABLE adherent_thematic_community DROP CONSTRAINT FK_DAB0B4EC25F06C53');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP CONSTRAINT FK_79E8AFFD25F06C53');
        $this->addSql('ALTER TABLE adherent_zone DROP CONSTRAINT FK_1C14D08525F06C53');
        $this->addSql('ALTER TABLE board_member DROP CONSTRAINT FK_DCFABEDF25F06C53');
        $this->addSql('ALTER TABLE referent_team_member DROP CONSTRAINT FK_6C006717597D3FE');
        $this->addSql('ALTER TABLE referent_team_member DROP CONSTRAINT FK_6C0067135E47E35');
        $this->addSql('ALTER TABLE municipal_manager_supervisor_role DROP CONSTRAINT FK_F304FF35E47E35');
        $this->addSql('ALTER TABLE territorial_council_membership DROP CONSTRAINT FK_2A99831625F06C53');
        $this->addSql('ALTER TABLE political_committee_membership DROP CONSTRAINT FK_FD85437B25F06C53');
        $this->addSql('ALTER TABLE committees_memberships DROP CONSTRAINT FK_E7A6490E25F06C53');
        $this->addSql('ALTER TABLE citizen_project_memberships DROP CONSTRAINT FK_2E4181625F06C53');
        $this->addSql('ALTER TABLE committee_feed_item DROP CONSTRAINT FK_4F1CDC80F675F31B');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP CONSTRAINT FK_CA001C72F675F31B');
        $this->addSql('ALTER TABLE adherent_charter DROP CONSTRAINT FK_D6F94F2B25F06C53');
        $this->addSql('ALTER TABLE certification_request DROP CONSTRAINT FK_6E7481A925F06C53');
        $this->addSql('ALTER TABLE certification_request DROP CONSTRAINT FK_6E7481A96EA98020');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP CONSTRAINT FK_421C13B98825BEFA');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP CONSTRAINT FK_421C13B9B7E7AE18');
        $this->addSql('ALTER TABLE adherent_commitment DROP CONSTRAINT FK_D239EF6F25F06C53');
        $this->addSql('ALTER TABLE adherent_mandate DROP CONSTRAINT FK_9C0C3D6025F06C53');
        $this->addSql('ALTER TABLE committee_provisional_supervisor DROP CONSTRAINT FK_E394C3D425F06C53');
        $this->addSql('ALTER TABLE referent_person_link DROP CONSTRAINT FK_BC75A60A25F06C53');
        $this->addSql('ALTER TABLE referent_managed_users_message DROP CONSTRAINT FK_1E41AC6125F06C53');
        $this->addSql('ALTER TABLE summaries DROP CONSTRAINT FK_66783CCA7597D3FE');
        $this->addSql('ALTER TABLE thematic_community_membership DROP CONSTRAINT FK_22B6AC0525F06C53');
        $this->addSql('ALTER TABLE jecoute_survey DROP CONSTRAINT FK_EC4948E5F675F31B');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP CONSTRAINT FK_6579E8E7F675F31B');
        $this->addSql('ALTER TABLE deputy_managed_users_message DROP CONSTRAINT FK_5AC419DD25F06C53');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP CONSTRAINT FK_CE975BDDF675F31B');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP CONSTRAINT FK_18589988F675F31B');
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP CONSTRAINT FK_9A9B5353F675F31B');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP CONSTRAINT FK_3F426D6D25F06C53');
        $this->addSql('ALTER TABLE voting_platform_voter DROP CONSTRAINT FK_AB02EC0225F06C53');
        $this->addSql('ALTER TABLE events DROP CONSTRAINT FK_5387574A876C4DDA');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA745F675F31B');
        $this->addSql('ALTER TABLE adherent_segment DROP CONSTRAINT FK_9DF0C7EBF675F31B');
        $this->addSql('ALTER TABLE adherent_messages DROP CONSTRAINT FK_D187C183F675F31B');
        $this->addSql('ALTER TABLE adherent_certification_histories DROP CONSTRAINT FK_732EE81A25F06C53');
        $this->addSql('ALTER TABLE user_authorizations DROP CONSTRAINT FK_40448230A76ED395');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP CONSTRAINT FK_CA42527CA76ED395');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP CONSTRAINT FK_BB493F83A76ED395');
        $this->addSql('ALTER TABLE referent_space_access_information DROP CONSTRAINT FK_CD8FDF4825F06C53');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP CONSTRAINT FK_A9919BF0B03A8386');
        $this->addSql('ALTER TABLE political_committee_feed_item DROP CONSTRAINT FK_54369E83F675F31B');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP CONSTRAINT FK_8D80D385F675F31B');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP CONSTRAINT FK_8D80D385B03A8386');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP CONSTRAINT FK_8D80D385896DBBDE');
        $this->addSql('ALTER TABLE territorial_council_membership_log DROP CONSTRAINT FK_2F6D242025F06C53');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP CONSTRAINT FK_45241D62F675F31B');
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP CONSTRAINT FK_78C1161DB03A8386');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        DROP 
          CONSTRAINT FK_1ECF756625F06C53');
        $this->addSql('ALTER TABLE elected_representative DROP CONSTRAINT FK_BF51F0FD25F06C53');
        $this->addSql('ALTER TABLE application_request_volunteer DROP CONSTRAINT FK_1139657025F06C53');
        $this->addSql('ALTER TABLE application_request_running_mate DROP CONSTRAINT FK_D1D6095625F06C53');
        $this->addSql('ALTER TABLE procuration_requests DROP CONSTRAINT FK_9769FD84888FDEEE');
        $this->addSql('ALTER TABLE vote_result DROP CONSTRAINT FK_1F8DB349B03A8386');
        $this->addSql('ALTER TABLE vote_result DROP CONSTRAINT FK_1F8DB349896DBBDE');
        $this->addSql('ALTER TABLE ministry_vote_result DROP CONSTRAINT FK_B9F11DAEB03A8386');
        $this->addSql('ALTER TABLE ministry_vote_result DROP CONSTRAINT FK_B9F11DAE896DBBDE');
        $this->addSql('ALTER TABLE elected_representatives_register DROP CONSTRAINT FK_55314F9525F06C53');
        $this->addSql('ALTER TABLE donation_transactions DROP CONSTRAINT FK_89D6D36B4DC1279C');
        $this->addSql('ALTER TABLE donators DROP CONSTRAINT FK_A902FDD7DE59CB1A');
        $this->addSql('ALTER TABLE donators DROP CONSTRAINT FK_A902FDD7ABF665A8');
        $this->addSql('ALTER TABLE donation_donation_tag DROP CONSTRAINT FK_F2D7087F4DC1279C');
        $this->addSql('ALTER TABLE donator_donator_tag DROP CONSTRAINT FK_6BAEC28C71F026E6');
        $this->addSql('ALTER TABLE board_member_roles DROP CONSTRAINT FK_1DD1E043C7BA2FD5');
        $this->addSql('ALTER TABLE saved_board_members DROP CONSTRAINT FK_32865A32FDCCD727');
        $this->addSql('ALTER TABLE saved_board_members DROP CONSTRAINT FK_32865A324821D202');
        $this->addSql('ALTER TABLE board_member_roles DROP CONSTRAINT FK_1DD1E043D60322AC');
        $this->addSql('ALTER TABLE adherent_subscription_type DROP CONSTRAINT FK_F93DC28AB6596C08');
        $this->addSql('ALTER TABLE adherent_email_subscription_histories DROP CONSTRAINT FK_51AD8354B6596C08');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA39BF75CAD');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3A132C3C5');
        $this->addSql('ALTER TABLE deputy_managed_users_message DROP CONSTRAINT FK_5AC419DDB08FA272');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3DC184E71');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP CONSTRAINT FK_8BE84DD56B99CC25');
        $this->addSql('ALTER TABLE referent_team_member_committee DROP CONSTRAINT FK_EC89860BFE4CA267');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA37034326B');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA31A912B27');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA339054338');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3E1B55931');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3E4A5D7A5');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA379DE69AA');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP CONSTRAINT FK_A713D9C2D96891C');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA39801977F');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA394E3BB99');
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP CONSTRAINT FK_BCDA0C151FB354CD');
        $this->addSql('ALTER TABLE territorial_council_quality DROP CONSTRAINT FK_C018E022E797FAB0');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP CONSTRAINT FK_DA86009A1FB354CD');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT FK_39885B61FB354CD');
        $this->addSql('ALTER TABLE political_committee_quality DROP CONSTRAINT FK_243D6D3A78632915');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT FK_9A04454FCC6DA91');
        $this->addSql('ALTER TABLE committee_merge_histories_merged_memberships DROP CONSTRAINT FK_CB8E336FFCC6DA91');
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP CONSTRAINT FK_368B01611FB354CD');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP CONSTRAINT FK_D269D0AABEF808A3');
        $this->addSql('ALTER TABLE adherent_adherent_tag DROP CONSTRAINT FK_DD297F82AED03543');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_themes DROP CONSTRAINT FK_DB4ED3145B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs DROP CONSTRAINT FK_75CEB995B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP CONSTRAINT FK_9A9B53535B6FEF7D');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP CONSTRAINT FK_256A5D7B5B6FEF7D');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA7455B6FEF7D');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3EA9FDD75');
        $this->addSql('ALTER TABLE referent DROP CONSTRAINT FK_FE9AAC6CEA9FDD75');
        $this->addSql('ALTER TABLE social_shares DROP CONSTRAINT FK_8E1413A0EA9FDD75');
        $this->addSql('ALTER TABLE formation_modules DROP CONSTRAINT FK_6B4806ACEA9FDD75');
        $this->addSql('ALTER TABLE formation_axes DROP CONSTRAINT FK_7E652CB6EA9FDD75');
        $this->addSql('ALTER TABLE home_blocks DROP CONSTRAINT FK_3EE9FCC5EA9FDD75');
        $this->addSql('ALTER TABLE legislative_candidates DROP CONSTRAINT FK_AE55AF9BEA9FDD75');
        $this->addSql('ALTER TABLE timeline_manifestos DROP CONSTRAINT FK_C6ED4403EA9FDD75');
        $this->addSql('ALTER TABLE timeline_themes DROP CONSTRAINT FK_8ADDB8F6EA9FDD75');
        $this->addSql('ALTER TABLE pages DROP CONSTRAINT FK_2074E5755B42DC0F');
        $this->addSql('ALTER TABLE pages DROP CONSTRAINT FK_2074E575EA9FDD75');
        $this->addSql('ALTER TABLE clarifications DROP CONSTRAINT FK_2FAB8972EA9FDD75');
        $this->addSql('ALTER TABLE custom_search_results DROP CONSTRAINT FK_38973E54EA9FDD75');
        $this->addSql('ALTER TABLE proposals DROP CONSTRAINT FK_A5BA3A8FEA9FDD75');
        $this->addSql('ALTER TABLE order_articles DROP CONSTRAINT FK_5E25D3D9EA9FDD75');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD3168EA9FDD75');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3CC72679B');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3FCCAF6D5');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP CONSTRAINT FK_F83208FAA7BF84E8');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA379645AD5');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA37657F304');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA393494FA8');
        $this->addSql('ALTER TABLE adherents DROP CONSTRAINT FK_562C7DA3122E5FF4');
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP CONSTRAINT FK_C52A163FFD98FA7A');
        $this->addSql('ALTER TABLE adherent_thematic_community DROP CONSTRAINT FK_DAB0B4EC1BE5825E');
        $this->addSql('ALTER TABLE thematic_community_membership DROP CONSTRAINT FK_22B6AC05FDA7B0BF');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP CONSTRAINT FK_79E8AFFD9C262DB3');
        $this->addSql('ALTER TABLE districts DROP CONSTRAINT FK_68E318DC9C262DB3');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP CONSTRAINT FK_8BE84DD59C262DB3');
        $this->addSql('ALTER TABLE senatorial_candidate_areas_tags DROP CONSTRAINT FK_F83208FA9C262DB3');
        $this->addSql('ALTER TABLE lre_area DROP CONSTRAINT FK_8D3B8F189C262DB3');
        $this->addSql('ALTER TABLE senator_area DROP CONSTRAINT FK_D229BBF7AEC89CE1');
        $this->addSql('ALTER TABLE citizen_project_referent_tag DROP CONSTRAINT FK_73ED204A9C262DB3');
        $this->addSql('ALTER TABLE designation_referent_tag DROP CONSTRAINT FK_7538F35A9C262DB3');
        $this->addSql('ALTER TABLE event_referent_tag DROP CONSTRAINT FK_D3C8F5BE9C262DB3');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F949C262DB3');
        $this->addSql('ALTER TABLE referent_user_filter_referent_tag DROP CONSTRAINT FK_F2BB20FE9C262DB3');
        $this->addSql('ALTER TABLE 
          adherent_email_subscription_history_referent_tag 
        DROP 
          CONSTRAINT FK_6FFBE6E89C262DB3');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag DROP CONSTRAINT FK_B6A8C7189C262DB3');
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP CONSTRAINT FK_78DBEB909C262DB3');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag DROP CONSTRAINT FK_D2B7A8C59C262DB3');
        $this->addSql('ALTER TABLE committee_referent_tag DROP CONSTRAINT FK_285EB1C59C262DB3');
        $this->addSql('ALTER TABLE volunteer_request_referent_tag DROP CONSTRAINT FK_DA2917429C262DB3');
        $this->addSql('ALTER TABLE running_mate_request_referent_tag DROP CONSTRAINT FK_53AB4FAB9C262DB3');
        $this->addSql('ALTER TABLE unregistration_referent_tag DROP CONSTRAINT FK_59B7AC49C262DB3');
        $this->addSql('ALTER TABLE republican_silence_referent_tag DROP CONSTRAINT FK_543DED269C262DB3');
        $this->addSql('ALTER TABLE adherent_zone DROP CONSTRAINT FK_1C14D0859F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_managed_areas DROP CONSTRAINT FK_DF8531749F2C3FAB');
        $this->addSql('ALTER TABLE candidate_managed_area DROP CONSTRAINT FK_C604D2EA9F2C3FAB');
        $this->addSql('ALTER TABLE referent_tags DROP CONSTRAINT FK_135D29D99F2C3FAB');
        $this->addSql('ALTER TABLE geo_zone_parent DROP CONSTRAINT FK_8E49B9DDD62C21B');
        $this->addSql('ALTER TABLE geo_zone_parent DROP CONSTRAINT FK_8E49B9D727ACA70');
        $this->addSql('ALTER TABLE projection_managed_users_zone DROP CONSTRAINT FK_E4D4ADCD9F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_survey DROP CONSTRAINT FK_EC4948E59F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_news DROP CONSTRAINT FK_34362099F2C3FAB');
        $this->addSql('ALTER TABLE event_zone DROP CONSTRAINT FK_BF208CAC9F2C3FAB');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F949F2C3FAB');
        $this->addSql('ALTER TABLE territorial_council_zone DROP CONSTRAINT FK_9467B41E9F2C3FAB');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP CONSTRAINT FK_38609146283AB2A9');
        $this->addSql('ALTER TABLE committee_zone DROP CONSTRAINT FK_37C5F2249F2C3FAB');
        $this->addSql('ALTER TABLE citizen_project_category_skills DROP CONSTRAINT FK_168C868A5585C142');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP CONSTRAINT FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE referent_areas DROP CONSTRAINT FK_75CEBC6C35E47E35');
        $this->addSql('ALTER TABLE referent_person_link DROP CONSTRAINT FK_BC75A60A35E47E35');
        $this->addSql('ALTER TABLE referent_areas DROP CONSTRAINT FK_75CEBC6CBD0F409C');
        $this->addSql('ALTER TABLE referent_person_link_committee DROP CONSTRAINT FK_1C97B2A5B3E4DE86');
        $this->addSql('ALTER TABLE 
          thematic_community_membership_user_list_definition 
        DROP 
          CONSTRAINT FK_58815EB9F74563E3');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F94F74563E3');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        DROP 
          CONSTRAINT FK_1ECF7566F74563E3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP CONSTRAINT FK_A9C53A24F74563E3');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP CONSTRAINT FK_BB3BCAEE998666D1');
        $this->addSql('ALTER TABLE summary_mission_type_wishes DROP CONSTRAINT FK_7F3FC70F2AC2D45C');
        $this->addSql('ALTER TABLE summary_skills DROP CONSTRAINT FK_2FD2B63C2AC2D45C');
        $this->addSql('ALTER TABLE member_summary_job_experiences DROP CONSTRAINT FK_72DD8B7F2AC2D45C');
        $this->addSql('ALTER TABLE member_summary_trainings DROP CONSTRAINT FK_C101987B2AC2D45C');
        $this->addSql('ALTER TABLE member_summary_languages DROP CONSTRAINT FK_70C88322AC2D45C');
        $this->addSql('ALTER TABLE referent_person_link DROP CONSTRAINT FK_BC75A60A810B5A42');
        $this->addSql('ALTER TABLE organizational_chart_item DROP CONSTRAINT FK_29C1CBACA977936C');
        $this->addSql('ALTER TABLE organizational_chart_item DROP CONSTRAINT FK_29C1CBAC727ACA70');
        $this->addSql('ALTER TABLE 
          thematic_community_membership_user_list_definition 
        DROP 
          CONSTRAINT FK_58815EB9403AE2A5');
        $this->addSql('ALTER TABLE thematic_community_membership DROP CONSTRAINT FK_22B6AC05E7A1254A');
        $this->addSql('ALTER TABLE department DROP CONSTRAINT FK_CD1DE18A98260155');
        $this->addSql('ALTER TABLE projection_managed_users_zone DROP CONSTRAINT FK_E4D4ADCDC679DD78');
        $this->addSql('ALTER TABLE certification_request DROP CONSTRAINT FK_6E7481A92FFD4FD3');
        $this->addSql('ALTER TABLE jecoute_survey DROP CONSTRAINT FK_EC4948E54B09E92C');
        $this->addSql('ALTER TABLE jecoute_news DROP CONSTRAINT FK_3436209B03A8386');
        $this->addSql('ALTER TABLE filesystem_file DROP CONSTRAINT FK_47F0AE28B03A8386');
        $this->addSql('ALTER TABLE filesystem_file DROP CONSTRAINT FK_47F0AE28896DBBDE');
        $this->addSql('ALTER TABLE administrator_export_history DROP CONSTRAINT FK_10499F014B09E92C');
        $this->addSql('ALTER TABLE adherent_certification_histories DROP CONSTRAINT FK_732EE81A4B09E92C');
        $this->addSql('ALTER TABLE committee_merge_histories DROP CONSTRAINT FK_BB95FBBC50FA8329');
        $this->addSql('ALTER TABLE committee_merge_histories DROP CONSTRAINT FK_BB95FBBCA8E1562');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        DROP 
          CONSTRAINT FK_1ECF75664B09E92C');
        $this->addSql('ALTER TABLE unregistrations DROP CONSTRAINT FK_F9E4AA0C5B30B80B');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP CONSTRAINT FK_A2FBFA81B3FE509D');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP CONSTRAINT FK_6579E8E7B3FE509D');
        $this->addSql('ALTER TABLE jecoute_choice DROP CONSTRAINT FK_80BD898B1E27F6BF');
        $this->addSql('ALTER TABLE jecoute_suggested_question DROP CONSTRAINT FK_8280E9DABF396750');
        $this->addSql('ALTER TABLE jecoute_survey_question DROP CONSTRAINT FK_A2FBFA811E27F6BF');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP CONSTRAINT FK_10DF117998666D1');
        $this->addSql('ALTER TABLE jecoute_data_answer_selected_choices DROP CONSTRAINT FK_10DF117259C0831');
        $this->addSql('ALTER TABLE jecoute_data_answer DROP CONSTRAINT FK_12FB393EA6DF29BA');
        $this->addSql('ALTER TABLE jecoute_data_answer DROP CONSTRAINT FK_12FB393E3C5110AB');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP CONSTRAINT FK_D075F5A9FCBF5E32');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP CONSTRAINT FK_A47BBD53FCBF5E32');
        $this->addSql('ALTER TABLE vote_result DROP CONSTRAINT FK_1F8DB349FCBF5E32');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP CONSTRAINT FK_9C1DD963FCBF5E32');
        $this->addSql('ALTER TABLE ministry_vote_result DROP CONSTRAINT FK_B9F11DAEFCBF5E32');
        $this->addSql('ALTER TABLE articles DROP CONSTRAINT FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE formation_axes DROP CONSTRAINT FK_7E652CB6D96C566B');
        $this->addSql('ALTER TABLE formation_files DROP CONSTRAINT FK_70BEDE2CAFC2B591');
        $this->addSql('ALTER TABLE formation_modules DROP CONSTRAINT FK_6B4806AC2E30CD41');
        $this->addSql('ALTER TABLE citizen_project_memberships DROP CONSTRAINT FK_2E41816B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP CONSTRAINT FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_project_referent_tag DROP CONSTRAINT FK_73ED204AB3584533');
        $this->addSql('ALTER TABLE events DROP CONSTRAINT FK_5387574AB3584533');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA745B3584533');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F94B3584533');
        $this->addSql('ALTER TABLE citizen_project_committee_supports DROP CONSTRAINT FK_F694C3BCB3584533');
        $this->addSql('ALTER TABLE legislative_candidates DROP CONSTRAINT FK_AE55AF9B23F5C396');
        $this->addSql('ALTER TABLE jecoute_region DROP CONSTRAINT FK_4E74226F39192B5C');
        $this->addSql('ALTER TABLE geo_department DROP CONSTRAINT FK_B460660498260155');
        $this->addSql('ALTER TABLE geo_city_canton DROP CONSTRAINT FK_A4AB64718D070D0B');
        $this->addSql('ALTER TABLE geo_city_district DROP CONSTRAINT FK_5C4191FB08FA272');
        $this->addSql('ALTER TABLE geo_region DROP CONSTRAINT FK_A4B3C808F92F3E70');
        $this->addSql('ALTER TABLE geo_foreign_district DROP CONSTRAINT FK_973BE1F198755666');
        $this->addSql('ALTER TABLE geo_consular_district DROP CONSTRAINT FK_BBFC552F72D24D35');
        $this->addSql('ALTER TABLE geo_country DROP CONSTRAINT FK_E465446472D24D35');
        $this->addSql('ALTER TABLE geo_borough DROP CONSTRAINT FK_144958748BAC62AF');
        $this->addSql('ALTER TABLE geo_city DROP CONSTRAINT FK_297C2D349D25CF90');
        $this->addSql('ALTER TABLE geo_city_district DROP CONSTRAINT FK_5C4191F8BAC62AF');
        $this->addSql('ALTER TABLE geo_city_canton DROP CONSTRAINT FK_A4AB64718BAC62AF');
        $this->addSql('ALTER TABLE geo_canton DROP CONSTRAINT FK_F04FC05FAE80F5DF');
        $this->addSql('ALTER TABLE geo_district DROP CONSTRAINT FK_DF782326AE80F5DF');
        $this->addSql('ALTER TABLE geo_city DROP CONSTRAINT FK_297C2D34AE80F5DF');
        $this->addSql('ALTER TABLE geo_city_community_department DROP CONSTRAINT FK_1E2D6D06AE80F5DF');
        $this->addSql('ALTER TABLE geo_city DROP CONSTRAINT FK_297C2D346D3B1930');
        $this->addSql('ALTER TABLE geo_city_community_department DROP CONSTRAINT FK_1E2D6D066D3B1930');
        $this->addSql('ALTER TABLE mooc_element_attachment_link DROP CONSTRAINT FK_324635C7B1828C9D');
        $this->addSql('ALTER TABLE mooc_element_attachment_file DROP CONSTRAINT FK_88759A26B1828C9D');
        $this->addSql('ALTER TABLE mooc_chapter DROP CONSTRAINT FK_A3EDA0D1255EEB87');
        $this->addSql('ALTER TABLE mooc_element_attachment_link DROP CONSTRAINT FK_324635C7653157F7');
        $this->addSql('ALTER TABLE mooc_elements DROP CONSTRAINT FK_691284C5579F4768');
        $this->addSql('ALTER TABLE mooc_element_attachment_file DROP CONSTRAINT FK_88759A265B5E2CEA');
        $this->addSql('ALTER TABLE order_section_order_article DROP CONSTRAINT FK_A956D4E46BF91E2F');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP CONSTRAINT FK_1517FC131BD1903D');
        $this->addSql('ALTER TABLE election_rounds DROP CONSTRAINT FK_37C02EA0A708DAFF');
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP CONSTRAINT FK_18589988E2904019');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA745E2904019');
        $this->addSql('ALTER TABLE ideas_workshop_answer DROP CONSTRAINT FK_256A5D7B1E27F6BF');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA7453A31E89B');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_needs DROP CONSTRAINT FK_75CEB99624AF264');
        $this->addSql('ALTER TABLE ideas_workshop_question DROP CONSTRAINT FK_111C43E4CC0B46A8');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_themes DROP CONSTRAINT FK_DB4ED31459027487');
        $this->addSql('ALTER TABLE ideas_workshop_thread DROP CONSTRAINT FK_CE975BDDAA334807');
        $this->addSql('ALTER TABLE ideas_workshop_answer_user_documents DROP CONSTRAINT FK_824E75E79C97E9FB');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP CONSTRAINT FK_CA001C7212469DE2');
        $this->addSql('ALTER TABLE consular_managed_area DROP CONSTRAINT FK_7937A51292CA96FD');
        $this->addSql('ALTER TABLE citizen_project_category_skills DROP CONSTRAINT FK_168C868A12469DE2');
        $this->addSql('ALTER TABLE citizen_projects DROP CONSTRAINT FK_651490212469DE2');
        $this->addSql('ALTER TABLE turnkey_projects DROP CONSTRAINT FK_CB66CFAE12469DE2');
        $this->addSql('ALTER TABLE interactive_invitation_has_choices DROP CONSTRAINT FK_31A811A2A35D7AF0');
        $this->addSql('ALTER TABLE interactive_invitation_has_choices DROP CONSTRAINT FK_31A811A2998666D1');
        $this->addSql('ALTER TABLE summary_skills DROP CONSTRAINT FK_2FD2B63C5585C142');
        $this->addSql('ALTER TABLE designation_referent_tag DROP CONSTRAINT FK_7538F35AFAC7D83F');
        $this->addSql('ALTER TABLE voting_platform_election DROP CONSTRAINT FK_4E144C94FAC7D83F');
        $this->addSql('ALTER TABLE committee_election DROP CONSTRAINT FK_2CA406E5FAC7D83F');
        $this->addSql('ALTER TABLE territorial_council_election DROP CONSTRAINT FK_14CBC36BFAC7D83F');
        $this->addSql('ALTER TABLE territorial_council DROP CONSTRAINT FK_B6DCA2A5B4D2A5D1');
        $this->addSql('ALTER TABLE committees DROP CONSTRAINT FK_A36198C6B4D2A5D1');
        $this->addSql('ALTER TABLE voting_platform_election_round_election_pool DROP CONSTRAINT FK_E6665F19FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_vote_result DROP CONSTRAINT FK_62C86890FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_vote DROP CONSTRAINT FK_DCBB2B7BFCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_election_round_result DROP CONSTRAINT FK_F2670966FCBF5E32');
        $this->addSql('ALTER TABLE voting_platform_election_round DROP CONSTRAINT FK_F15D87B7A708DAFF');
        $this->addSql('ALTER TABLE voting_platform_voters_list DROP CONSTRAINT FK_3C73500DA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_pool DROP CONSTRAINT FK_7225D6EFA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_result DROP CONSTRAINT FK_67EFA0E4A708DAFF');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP CONSTRAINT FK_7AAD259FA708DAFF');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP CONSTRAINT FK_B009F31145EB7186');
        $this->addSql('ALTER TABLE voting_platform_voters_list_voter DROP CONSTRAINT FK_7CC26956FB0C8C84');
        $this->addSql('ALTER TABLE voting_platform_candidate DROP CONSTRAINT FK_3F426D6D5F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result DROP CONSTRAINT FK_7249D5375F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP CONSTRAINT FK_B009F3115F0A9B94');
        $this->addSql('ALTER TABLE voting_platform_voters_list_voter DROP CONSTRAINT FK_7CC26956EBB4B8AD');
        $this->addSql('ALTER TABLE voting_platform_vote DROP CONSTRAINT FK_DCBB2B7BEBB4B8AD');
        $this->addSql('ALTER TABLE voting_platform_election_round_election_pool DROP CONSTRAINT FK_E6665F19C1E98F21');
        $this->addSql('ALTER TABLE voting_platform_candidate_group DROP CONSTRAINT FK_2C1A353AC1E98F21');
        $this->addSql('ALTER TABLE voting_platform_election_pool_result DROP CONSTRAINT FK_13C1C73FC1E98F21');
        $this->addSql('ALTER TABLE voting_platform_vote_choice DROP CONSTRAINT FK_B009F311C1E98F21');
        $this->addSql('ALTER TABLE voting_platform_election_round_result DROP CONSTRAINT FK_F267096619FCFB29');
        $this->addSql('ALTER TABLE voting_platform_candidate_group_result DROP CONSTRAINT FK_7249D537B5BA5CC5');
        $this->addSql('ALTER TABLE voting_platform_election_pool_result DROP CONSTRAINT FK_13C1C73F8FFC0F0B');
        $this->addSql('ALTER TABLE committee_feed_item DROP CONSTRAINT FK_4F1CDC8071F7E88B');
        $this->addSql('ALTER TABLE events_invitations DROP CONSTRAINT FK_B94D5AAD71F7E88B');
        $this->addSql('ALTER TABLE events_registrations DROP CONSTRAINT FK_EEFA30C071F7E88B');
        $this->addSql('ALTER TABLE event_referent_tag DROP CONSTRAINT FK_D3C8F5BE71F7E88B');
        $this->addSql('ALTER TABLE event_zone DROP CONSTRAINT FK_BF208CAC3B1C4B73');
        $this->addSql('ALTER TABLE event_user_documents DROP CONSTRAINT FK_7D14491F71F7E88B');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA745A2DD3412');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA74583B12DAC');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT FK_9A044544E891720');
        $this->addSql('ALTER TABLE committee_feed_item_user_documents DROP CONSTRAINT FK_D269D0AA6A24B1A2');
        $this->addSql('ALTER TABLE ideas_workshop_answer_user_documents DROP CONSTRAINT FK_824E75E76A24B1A2');
        $this->addSql('ALTER TABLE event_user_documents DROP CONSTRAINT FK_7D14491F6A24B1A2');
        $this->addSql('ALTER TABLE filesystem_file DROP CONSTRAINT FK_47F0AE28727ACA70');
        $this->addSql('ALTER TABLE filesystem_file_permission DROP CONSTRAINT FK_BD623E4C93CB796C');
        $this->addSql('ALTER TABLE proposal_proposal_theme DROP CONSTRAINT FK_6B80CE41B85948AF');
        $this->addSql('ALTER TABLE article_proposal_theme DROP CONSTRAINT FK_F6B9A221B85948AF');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP CONSTRAINT FK_B83D81AE5DA37D00');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP CONSTRAINT FK_EB8A7B0C5DA37D00');
        $this->addSql('ALTER TABLE timeline_measure_translations DROP CONSTRAINT FK_5C9EB6072C2AC5D3');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP CONSTRAINT FK_B83D81AECCFA12B8');
        $this->addSql('ALTER TABLE timeline_profile_translations DROP CONSTRAINT FK_41B3A6DA2C2AC5D3');
        $this->addSql('ALTER TABLE timeline_measures DROP CONSTRAINT FK_BA475ED737E924');
        $this->addSql('ALTER TABLE timeline_manifesto_translations DROP CONSTRAINT FK_F7BD6C172C2AC5D3');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP CONSTRAINT FK_EB8A7B0C59027487');
        $this->addSql('ALTER TABLE timeline_theme_translations DROP CONSTRAINT FK_F81F72932C2AC5D3');
        $this->addSql('ALTER TABLE events_categories DROP CONSTRAINT FK_EF0AF3E9A267D842');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT FK_9A044548D4924C4');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F94FAF04979');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP CONSTRAINT FK_CFABD309537A1329');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP CONSTRAINT FK_CFABD3094BD2A4C0');
        $this->addSql('ALTER TABLE mailchimp_campaign_mailchimp_segment DROP CONSTRAINT FK_901CE107828112CC');
        $this->addSql('ALTER TABLE adherent_messages DROP CONSTRAINT FK_D187C183D395B25E');
        $this->addSql('ALTER TABLE referent_user_filter_referent_tag DROP CONSTRAINT FK_F2BB20FEEFAB50C4');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP CONSTRAINT FK_D075F5A9E15E419B');
        $this->addSql('ALTER TABLE procuration_requests DROP CONSTRAINT FK_9769FD842F1B6663');
        $this->addSql('ALTER TABLE assessor_role_association DROP CONSTRAINT FK_B93395C2F3F90B30');
        $this->addSql('ALTER TABLE assessor_requests DROP CONSTRAINT FK_26BC800F3F90B30');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP CONSTRAINT FK_1517FC13F3F90B30');
        $this->addSql('ALTER TABLE vote_result DROP CONSTRAINT FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE committee_merge_histories_merged_memberships DROP CONSTRAINT FK_CB8E336F9379ED92');
        $this->addSql('ALTER TABLE 
          adherent_email_subscription_history_referent_tag 
        DROP 
          CONSTRAINT FK_6FFBE6E88FCB8132');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag DROP CONSTRAINT FK_B6A8C718123C64CE');
        $this->addSql('ALTER TABLE oauth_refresh_tokens DROP CONSTRAINT FK_5AB6872CCB2688');
        $this->addSql('ALTER TABLE user_authorizations DROP CONSTRAINT FK_4044823019EB6921');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP CONSTRAINT FK_CA42527C19EB6921');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP CONSTRAINT FK_BB493F8319EB6921');
        $this->addSql('ALTER TABLE web_hooks DROP CONSTRAINT FK_CDB836AD19EB6921');
        $this->addSql('ALTER TABLE territorial_council_official_report_document DROP CONSTRAINT FK_78C1161D4BD2A4C0');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT FK_39885B6A708DAFF');
        $this->addSql('ALTER TABLE political_committee_membership DROP CONSTRAINT FK_FD85437BC7A72');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F94C7A72');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP CONSTRAINT FK_A9919BF0C7A72');
        $this->addSql('ALTER TABLE political_committee_feed_item DROP CONSTRAINT FK_54369E83C7A72');
        $this->addSql('ALTER TABLE territorial_council_official_report DROP CONSTRAINT FK_8D80D385C7A72');
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP CONSTRAINT FK_BCDA0C15998666D1');
        $this->addSql('ALTER TABLE territorial_council_election DROP CONSTRAINT FK_14CBC36B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_election_poll_choice DROP CONSTRAINT FK_63EBCF6B8649F5F1');
        $this->addSql('ALTER TABLE territorial_council_membership DROP CONSTRAINT FK_2A998316AAA61A99');
        $this->addSql('ALTER TABLE adherent_mandate DROP CONSTRAINT FK_9C0C3D60AAA61A99');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP CONSTRAINT FK_7AAD259FAAA61A99');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F94AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP CONSTRAINT FK_A9919BF0AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_election DROP CONSTRAINT FK_14CBC36BAAA61A99');
        $this->addSql('ALTER TABLE political_committee DROP CONSTRAINT FK_39FAEE95AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP CONSTRAINT FK_45241D62AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP CONSTRAINT FK_78DBEB90AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_zone DROP CONSTRAINT FK_9467B41EAAA61A99');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT FK_39885B6A35D7AF0');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP CONSTRAINT FK_39885B68D4924C4');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag DROP CONSTRAINT FK_D2B7A8C5BE31A103');
        $this->addSql('ALTER TABLE elected_representative_zone_parent DROP CONSTRAINT FK_CECA906FDD62C21B');
        $this->addSql('ALTER TABLE elected_representative_zone_parent DROP CONSTRAINT FK_CECA906F727ACA70');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP CONSTRAINT FK_386091469F2C3FAB');
        $this->addSql('ALTER TABLE elected_representative_zone DROP CONSTRAINT FK_C52FC4A712469DE2');
        $this->addSql('ALTER TABLE elected_representative_political_function DROP CONSTRAINT FK_303BAF416C1129CD');
        $this->addSql('ALTER TABLE elected_representative_political_function DROP CONSTRAINT FK_303BAF41D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_social_network_link DROP CONSTRAINT FK_231377B5D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP CONSTRAINT FK_38609146D38DA5D3');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        DROP 
          CONSTRAINT FK_1ECF7566D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_sponsorship DROP CONSTRAINT FK_CA6D486D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_label DROP CONSTRAINT FK_D8143704D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP CONSTRAINT FK_A9C53A24D38DA5D3');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP CONSTRAINT FK_BB3BCAEEA35D7AF0');
        $this->addSql('ALTER TABLE referent_team_member_committee DROP CONSTRAINT FK_EC89860BED1A100B');
        $this->addSql('ALTER TABLE committees_memberships DROP CONSTRAINT FK_E7A6490EED1A100B');
        $this->addSql('ALTER TABLE committee_feed_item DROP CONSTRAINT FK_4F1CDC80ED1A100B');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP CONSTRAINT FK_CA001C72ED1A100B');
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP CONSTRAINT FK_C52A163FED1A100B');
        $this->addSql('ALTER TABLE adherent_mandate DROP CONSTRAINT FK_9C0C3D60ED1A100B');
        $this->addSql('ALTER TABLE committee_provisional_supervisor DROP CONSTRAINT FK_E394C3D4ED1A100B');
        $this->addSql('ALTER TABLE referent_person_link_committee DROP CONSTRAINT FK_1C97B2A5ED1A100B');
        $this->addSql('ALTER TABLE voting_platform_election_entity DROP CONSTRAINT FK_7AAD259FED1A100B');
        $this->addSql('ALTER TABLE events DROP CONSTRAINT FK_5387574AED1A100B');
        $this->addSql('ALTER TABLE committee_election DROP CONSTRAINT FK_2CA406E5ED1A100B');
        $this->addSql('ALTER TABLE reports DROP CONSTRAINT FK_F11FA745ED1A100B');
        $this->addSql('ALTER TABLE adherent_message_filters DROP CONSTRAINT FK_28CA9F94ED1A100B');
        $this->addSql('ALTER TABLE committee_merge_histories DROP CONSTRAINT FK_BB95FBBC3BF0CCB3');
        $this->addSql('ALTER TABLE committee_merge_histories DROP CONSTRAINT FK_BB95FBBC5C34CBC4');
        $this->addSql('ALTER TABLE committees_membership_histories DROP CONSTRAINT FK_4BBAE2C7ED1A100B');
        $this->addSql('ALTER TABLE committee_referent_tag DROP CONSTRAINT FK_285EB1C5ED1A100B');
        $this->addSql('ALTER TABLE committee_zone DROP CONSTRAINT FK_37C5F224ED1A100B');
        $this->addSql('ALTER TABLE citizen_project_committee_supports DROP CONSTRAINT FK_F694C3BCED1A100B');
        $this->addSql('ALTER TABLE donation_donation_tag DROP CONSTRAINT FK_F2D7087F790547EA');
        $this->addSql('ALTER TABLE citizen_projects DROP CONSTRAINT FK_6514902B5315DF4');
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file DROP CONSTRAINT FK_67BF8377B5315DF4');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP CONSTRAINT FK_7F8C5C1EB8D6887');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP CONSTRAINT FK_5427AF53B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP CONSTRAINT FK_6F3FA269B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_referent_tag DROP CONSTRAINT FK_DA291742B8D6887');
        $this->addSql('ALTER TABLE volunteer_request_application_request_tag DROP CONSTRAINT FK_6F3FA2699644FEDA');
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP CONSTRAINT FK_9D534FCF9644FEDA');
        $this->addSql('ALTER TABLE volunteer_request_technical_skill DROP CONSTRAINT FK_7F8C5C1EE98F0EFD');
        $this->addSql('ALTER TABLE volunteer_request_theme DROP CONSTRAINT FK_5427AF5359027487');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP CONSTRAINT FK_A732622759027487');
        $this->addSql('ALTER TABLE running_mate_request_theme DROP CONSTRAINT FK_A7326227CEDF4387');
        $this->addSql('ALTER TABLE running_mate_request_application_request_tag DROP CONSTRAINT FK_9D534FCFCEDF4387');
        $this->addSql('ALTER TABLE running_mate_request_referent_tag DROP CONSTRAINT FK_53AB4FABCEDF4387');
        $this->addSql('ALTER TABLE committee_candidacy DROP CONSTRAINT FK_9A04454A35D7AF0');
        $this->addSql('ALTER TABLE chez_vous_departments DROP CONSTRAINT FK_29E7DD5798260155');
        $this->addSql('ALTER TABLE chez_vous_measures DROP CONSTRAINT FK_E6E8973EC54C8C93');
        $this->addSql('ALTER TABLE chez_vous_measures DROP CONSTRAINT FK_E6E8973E8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_markers DROP CONSTRAINT FK_452F890F8BAC62AF');
        $this->addSql('ALTER TABLE chez_vous_cities DROP CONSTRAINT FK_A42D9BEDAE80F5DF');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP CONSTRAINT FK_A47BBD53128D9C53');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP CONSTRAINT FK_F004297F5DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_project DROP CONSTRAINT FK_8E8E96D55DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP CONSTRAINT FK_F004297FBAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP CONSTRAINT FK_9F63872BAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_sub_approach DROP CONSTRAINT FK_735C1D0115140614');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP CONSTRAINT FK_9F63872166D1F9C');
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP CONSTRAINT FK_213A5F1EF0ED738A');
        $this->addSql('ALTER TABLE proposal_proposal_theme DROP CONSTRAINT FK_6B80CE41F4792058');
        $this->addSql('ALTER TABLE order_section_order_article DROP CONSTRAINT FK_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE turnkey_project_turnkey_project_file DROP CONSTRAINT FK_67BF83777D06E1CD');
        $this->addSql('ALTER TABLE article_proposal_theme DROP CONSTRAINT FK_F6B9A2217294869C');
        $this->addSql('ALTER TABLE social_shares DROP CONSTRAINT FK_8E1413A085040FAD');
        $this->addSql('ALTER TABLE mailchimp_campaign_mailchimp_segment DROP CONSTRAINT FK_901CE107D21E482E');
        $this->addSql('ALTER TABLE unregistration_referent_tag DROP CONSTRAINT FK_59B7AC414D824CA');
        $this->addSql('ALTER TABLE republican_silence_referent_tag DROP CONSTRAINT FK_543DED2612359909');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP CONSTRAINT FK_A713D9C28BAC62AF');
        $this->addSql('ALTER TABLE vote_result DROP CONSTRAINT FK_1F8DB3498BAC62AF');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D18BAC62AF');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP CONSTRAINT FK_9C1DD9638BAC62AF');
        $this->addSql('ALTER TABLE ministry_vote_result DROP CONSTRAINT FK_B9F11DAE8BAC62AF');
        $this->addSql('ALTER TABLE districts DROP CONSTRAINT FK_68E318DC80E32C3E');
        $this->addSql('ALTER TABLE geo_zone DROP CONSTRAINT FK_A4CCEF0780E32C3E');
        $this->addSql('ALTER TABLE geo_region DROP CONSTRAINT FK_A4B3C80880E32C3E');
        $this->addSql('ALTER TABLE geo_consular_district DROP CONSTRAINT FK_BBFC552F80E32C3E');
        $this->addSql('ALTER TABLE geo_canton DROP CONSTRAINT FK_F04FC05F80E32C3E');
        $this->addSql('ALTER TABLE geo_district DROP CONSTRAINT FK_DF78232680E32C3E');
        $this->addSql('ALTER TABLE geo_borough DROP CONSTRAINT FK_1449587480E32C3E');
        $this->addSql('ALTER TABLE geo_country DROP CONSTRAINT FK_E465446480E32C3E');
        $this->addSql('ALTER TABLE geo_custom_zone DROP CONSTRAINT FK_ABE4DB5A80E32C3E');
        $this->addSql('ALTER TABLE geo_foreign_district DROP CONSTRAINT FK_973BE1F180E32C3E');
        $this->addSql('ALTER TABLE geo_city DROP CONSTRAINT FK_297C2D3480E32C3E');
        $this->addSql('ALTER TABLE geo_department DROP CONSTRAINT FK_B460660480E32C3E');
        $this->addSql('ALTER TABLE geo_city_community DROP CONSTRAINT FK_E5805E0880E32C3E');
        $this->addSql('ALTER TABLE cities DROP CONSTRAINT FK_D95DB16BAE80F5DF');
        $this->addSql('ALTER TABLE mooc_elements DROP CONSTRAINT FK_691284C53DA5256D');
        $this->addSql('ALTER TABLE mooc DROP CONSTRAINT FK_9D5D3B55684DD106');
        $this->addSql('ALTER TABLE mooc DROP CONSTRAINT FK_9D5D3B5543C8160D');
        $this->addSql('ALTER TABLE summary_mission_type_wishes DROP CONSTRAINT FK_7F3FC70F547018DE');
        $this->addSql('ALTER TABLE list_total_result DROP CONSTRAINT FK_A19B071E45EB7186');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1B29FABBC');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1E4A014FA');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1781FEED9');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1E449D110');
        $this->addSql('ALTER TABLE list_total_result DROP CONSTRAINT FK_A19B071E3DAE168B');
        $this->addSql('ALTER TABLE election_city_contact DROP CONSTRAINT FK_D04AFB68BAC62AF');
        $this->addSql('ALTER TABLE election_city_partner DROP CONSTRAINT FK_704D77988BAC62AF');
        $this->addSql('ALTER TABLE vote_result_list DROP CONSTRAINT FK_677ED502DB567AF4');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1354DEDE5');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D15EC54712');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1F543170A');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1EBF42685');
        $this->addSql('ALTER TABLE election_city_card DROP CONSTRAINT FK_EB01E8D1B86B270B');
        $this->addSql('ALTER TABLE ministry_list_total_result DROP CONSTRAINT FK_99D1332580711B75');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP CONSTRAINT FK_6579E8E794A4C7D4');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP CONSTRAINT FK_CA42527C94A4C7D4');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP CONSTRAINT FK_BB493F8394A4C7D4');
        $this->addSql('DROP TABLE donation_transactions');
        $this->addSql('DROP TABLE donators');
        $this->addSql('DROP TABLE donator_donator_tag');
        $this->addSql('DROP TABLE adherents');
        $this->addSql('DROP TABLE adherent_subscription_type');
        $this->addSql('DROP TABLE adherent_adherent_tag');
        $this->addSql('DROP TABLE adherent_thematic_community');
        $this->addSql('DROP TABLE adherent_referent_tag');
        $this->addSql('DROP TABLE adherent_zone');
        $this->addSql('DROP TABLE donations');
        $this->addSql('DROP TABLE donation_donation_tag');
        $this->addSql('DROP TABLE donator_tags');
        $this->addSql('DROP TABLE donator_kinship');
        $this->addSql('DROP TABLE donator_identifier');
        $this->addSql('DROP TABLE board_member');
        $this->addSql('DROP TABLE board_member_roles');
        $this->addSql('DROP TABLE saved_board_members');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE subscription_type');
        $this->addSql('DROP TABLE districts');
        $this->addSql('DROP TABLE referent_managed_areas');
        $this->addSql('DROP TABLE referent_managed_areas_tags');
        $this->addSql('DROP TABLE referent_team_member');
        $this->addSql('DROP TABLE referent_team_member_committee');
        $this->addSql('DROP TABLE coordinator_managed_areas');
        $this->addSql('DROP TABLE procuration_managed_areas');
        $this->addSql('DROP TABLE assessor_managed_areas');
        $this->addSql('DROP TABLE assessor_role_association');
        $this->addSql('DROP TABLE municipal_manager_role_association');
        $this->addSql('DROP TABLE municipal_manager_role_association_cities');
        $this->addSql('DROP TABLE municipal_manager_supervisor_role');
        $this->addSql('DROP TABLE jecoute_managed_areas');
        $this->addSql('DROP TABLE territorial_council_membership');
        $this->addSql('DROP TABLE political_committee_membership');
        $this->addSql('DROP TABLE committees_memberships');
        $this->addSql('DROP TABLE citizen_project_memberships');
        $this->addSql('DROP TABLE committee_feed_item');
        $this->addSql('DROP TABLE committee_feed_item_user_documents');
        $this->addSql('DROP TABLE adherent_tags');
        $this->addSql('DROP TABLE ideas_workshop_idea');
        $this->addSql('DROP TABLE ideas_workshop_ideas_themes');
        $this->addSql('DROP TABLE ideas_workshop_ideas_needs');
        $this->addSql('DROP TABLE medias');
        $this->addSql('DROP TABLE municipal_chief_areas');
        $this->addSql('DROP TABLE senatorial_candidate_areas');
        $this->addSql('DROP TABLE senatorial_candidate_areas_tags');
        $this->addSql('DROP TABLE lre_area');
        $this->addSql('DROP TABLE candidate_managed_area');
        $this->addSql('DROP TABLE adherent_charter');
        $this->addSql('DROP TABLE senator_area');
        $this->addSql('DROP TABLE consular_managed_area');
        $this->addSql('DROP TABLE certification_request');
        $this->addSql('DROP TABLE my_team_delegated_access');
        $this->addSql('DROP TABLE my_team_delegate_access_committee');
        $this->addSql('DROP TABLE adherent_commitment');
        $this->addSql('DROP TABLE thematic_community');
        $this->addSql('DROP TABLE adherent_mandate');
        $this->addSql('DROP TABLE committee_provisional_supervisor');
        $this->addSql('DROP TABLE referent_tags');
        $this->addSql('DROP TABLE geo_zone');
        $this->addSql('DROP TABLE geo_zone_parent');
        $this->addSql('DROP TABLE citizen_project_skills');
        $this->addSql('DROP TABLE referent');
        $this->addSql('DROP TABLE referent_areas');
        $this->addSql('DROP TABLE referent_area');
        $this->addSql('DROP TABLE referent_person_link');
        $this->addSql('DROP TABLE referent_person_link_committee');
        $this->addSql('DROP TABLE user_list_definition');
        $this->addSql('DROP TABLE je_marche_reports');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('DROP TABLE citizen_project_category_skills');
        $this->addSql('DROP TABLE referent_managed_users_message');
        $this->addSql('DROP TABLE ton_macron_choices');
        $this->addSql('DROP TABLE summaries');
        $this->addSql('DROP TABLE summary_mission_type_wishes');
        $this->addSql('DROP TABLE summary_skills');
        $this->addSql('DROP TABLE live_links');
        $this->addSql('DROP TABLE facebook_profiles');
        $this->addSql('DROP TABLE organizational_chart_item');
        $this->addSql('DROP TABLE events_invitations');
        $this->addSql('DROP TABLE thematic_community_membership');
        $this->addSql('DROP TABLE thematic_community_membership_user_list_definition');
        $this->addSql('DROP TABLE thematic_community_contact');
        $this->addSql('DROP TABLE events_registrations');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE projection_managed_users');
        $this->addSql('DROP TABLE projection_managed_users_zone');
        $this->addSql('DROP TABLE administrators');
        $this->addSql('DROP TABLE adherent_activation_keys');
        $this->addSql('DROP TABLE redirections');
        $this->addSql('DROP TABLE jecoute_survey');
        $this->addSql('DROP TABLE jecoute_region');
        $this->addSql('DROP TABLE jecoute_question');
        $this->addSql('DROP TABLE jecoute_choice');
        $this->addSql('DROP TABLE jecoute_suggested_question');
        $this->addSql('DROP TABLE jecoute_news');
        $this->addSql('DROP TABLE jecoute_data_answer');
        $this->addSql('DROP TABLE jecoute_data_answer_selected_choices');
        $this->addSql('DROP TABLE jecoute_survey_question');
        $this->addSql('DROP TABLE jecoute_data_survey');
        $this->addSql('DROP TABLE election_rounds');
        $this->addSql('DROP TABLE articles_categories');
        $this->addSql('DROP TABLE social_shares');
        $this->addSql('DROP TABLE formation_paths');
        $this->addSql('DROP TABLE formation_files');
        $this->addSql('DROP TABLE formation_modules');
        $this->addSql('DROP TABLE formation_axes');
        $this->addSql('DROP TABLE citizen_projects');
        $this->addSql('DROP TABLE citizen_projects_skills');
        $this->addSql('DROP TABLE citizen_project_referent_tag');
        $this->addSql('DROP TABLE legislative_district_zones');
        $this->addSql('DROP TABLE geo_region');
        $this->addSql('DROP TABLE geo_consular_district');
        $this->addSql('DROP TABLE geo_canton');
        $this->addSql('DROP TABLE geo_district');
        $this->addSql('DROP TABLE geo_borough');
        $this->addSql('DROP TABLE geo_country');
        $this->addSql('DROP TABLE geo_custom_zone');
        $this->addSql('DROP TABLE geo_foreign_district');
        $this->addSql('DROP TABLE geo_city');
        $this->addSql('DROP TABLE geo_city_district');
        $this->addSql('DROP TABLE geo_city_canton');
        $this->addSql('DROP TABLE geo_department');
        $this->addSql('DROP TABLE geo_city_community');
        $this->addSql('DROP TABLE geo_city_community_department');
        $this->addSql('DROP TABLE mooc_elements');
        $this->addSql('DROP TABLE mooc_element_attachment_link');
        $this->addSql('DROP TABLE mooc_element_attachment_file');
        $this->addSql('DROP TABLE mooc');
        $this->addSql('DROP TABLE mooc_attachment_link');
        $this->addSql('DROP TABLE mooc_chapter');
        $this->addSql('DROP TABLE mooc_attachment_file');
        $this->addSql('DROP TABLE order_sections');
        $this->addSql('DROP TABLE deputy_managed_users_message');
        $this->addSql('DROP TABLE assessor_requests');
        $this->addSql('DROP TABLE assessor_requests_vote_place_wishes');
        $this->addSql('DROP TABLE elections');
        $this->addSql('DROP TABLE banned_adherent');
        $this->addSql('DROP TABLE ideas_workshop_idea_notification_dates');
        $this->addSql('DROP TABLE ideas_workshop_thread');
        $this->addSql('DROP TABLE ideas_workshop_question');
        $this->addSql('DROP TABLE ideas_workshop_comment');
        $this->addSql('DROP TABLE ideas_workshop_need');
        $this->addSql('DROP TABLE ideas_workshop_consultation');
        $this->addSql('DROP TABLE ideas_workshop_vote');
        $this->addSql('DROP TABLE ideas_workshop_guideline');
        $this->addSql('DROP TABLE ideas_workshop_theme');
        $this->addSql('DROP TABLE ideas_workshop_answer');
        $this->addSql('DROP TABLE ideas_workshop_answer_user_documents');
        $this->addSql('DROP TABLE ideas_workshop_consultation_report');
        $this->addSql('DROP TABLE ideas_workshop_category');
        $this->addSql('DROP TABLE home_blocks');
        $this->addSql('DROP TABLE consular_district');
        $this->addSql('DROP TABLE citizen_project_categories');
        $this->addSql('DROP TABLE interactive_invitations');
        $this->addSql('DROP TABLE interactive_invitation_has_choices');
        $this->addSql('DROP TABLE interactive_choices');
        $this->addSql('DROP TABLE legislative_candidates');
        $this->addSql('DROP TABLE events_categories');
        $this->addSql('DROP TABLE skills');
        $this->addSql('DROP TABLE designation');
        $this->addSql('DROP TABLE designation_referent_tag');
        $this->addSql('DROP TABLE voting_platform_election_round');
        $this->addSql('DROP TABLE voting_platform_election_round_election_pool');
        $this->addSql('DROP TABLE voting_platform_candidate');
        $this->addSql('DROP TABLE voting_platform_election');
        $this->addSql('DROP TABLE voting_platform_vote_result');
        $this->addSql('DROP TABLE voting_platform_voters_list');
        $this->addSql('DROP TABLE voting_platform_voters_list_voter');
        $this->addSql('DROP TABLE voting_platform_vote');
        $this->addSql('DROP TABLE voting_platform_candidate_group');
        $this->addSql('DROP TABLE voting_platform_voter');
        $this->addSql('DROP TABLE voting_platform_election_pool');
        $this->addSql('DROP TABLE voting_platform_election_result');
        $this->addSql('DROP TABLE voting_platform_candidate_group_result');
        $this->addSql('DROP TABLE voting_platform_election_pool_result');
        $this->addSql('DROP TABLE voting_platform_election_round_result');
        $this->addSql('DROP TABLE voting_platform_election_entity');
        $this->addSql('DROP TABLE voting_platform_vote_choice');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE event_referent_tag');
        $this->addSql('DROP TABLE event_zone');
        $this->addSql('DROP TABLE event_user_documents');
        $this->addSql('DROP TABLE committee_election');
        $this->addSql('DROP TABLE user_documents');
        $this->addSql('DROP TABLE filesystem_file');
        $this->addSql('DROP TABLE filesystem_file_permission');
        $this->addSql('DROP TABLE adherent_change_email_token');
        $this->addSql('DROP TABLE proposals_themes');
        $this->addSql('DROP TABLE timeline_measures');
        $this->addSql('DROP TABLE timeline_measures_profiles');
        $this->addSql('DROP TABLE timeline_themes_measures');
        $this->addSql('DROP TABLE timeline_theme_translations');
        $this->addSql('DROP TABLE timeline_profiles');
        $this->addSql('DROP TABLE timeline_measure_translations');
        $this->addSql('DROP TABLE timeline_manifestos');
        $this->addSql('DROP TABLE timeline_manifesto_translations');
        $this->addSql('DROP TABLE timeline_themes');
        $this->addSql('DROP TABLE timeline_profile_translations');
        $this->addSql('DROP TABLE biography_executive_office_member');
        $this->addSql('DROP TABLE event_group_category');
        $this->addSql('DROP TABLE committee_candidacy');
        $this->addSql('DROP TABLE reports');
        $this->addSql('DROP TABLE adherent_segment');
        $this->addSql('DROP TABLE adherent_messages');
        $this->addSql('DROP TABLE mailchimp_campaign_report');
        $this->addSql('DROP TABLE mailchimp_campaign');
        $this->addSql('DROP TABLE mailchimp_campaign_mailchimp_segment');
        $this->addSql('DROP TABLE adherent_message_filters');
        $this->addSql('DROP TABLE referent_user_filter_referent_tag');
        $this->addSql('DROP TABLE pages');
        $this->addSql('DROP TABLE procuration_proxies');
        $this->addSql('DROP TABLE procuration_proxies_to_election_rounds');
        $this->addSql('DROP TABLE vote_place');
        $this->addSql('DROP TABLE administrator_export_history');
        $this->addSql('DROP TABLE adherent_certification_histories');
        $this->addSql('DROP TABLE committee_merge_histories');
        $this->addSql('DROP TABLE committee_merge_histories_merged_memberships');
        $this->addSql('DROP TABLE adherent_email_subscription_histories');
        $this->addSql('DROP TABLE adherent_email_subscription_history_referent_tag');
        $this->addSql('DROP TABLE committees_membership_histories');
        $this->addSql('DROP TABLE committee_membership_history_referent_tag');
        $this->addSql('DROP TABLE user_authorizations');
        $this->addSql('DROP TABLE oauth_refresh_tokens');
        $this->addSql('DROP TABLE oauth_access_tokens');
        $this->addSql('DROP TABLE oauth_clients');
        $this->addSql('DROP TABLE oauth_auth_codes');
        $this->addSql('DROP TABLE referent_space_access_information');
        $this->addSql('DROP TABLE territorial_council_convocation');
        $this->addSql('DROP TABLE political_committee_feed_item');
        $this->addSql('DROP TABLE territorial_council_official_report');
        $this->addSql('DROP TABLE territorial_council_membership_log');
        $this->addSql('DROP TABLE territorial_council_election');
        $this->addSql('DROP TABLE political_committee');
        $this->addSql('DROP TABLE territorial_council_feed_item');
        $this->addSql('DROP TABLE territorial_council_election_poll_choice');
        $this->addSql('DROP TABLE territorial_council_election_poll_vote');
        $this->addSql('DROP TABLE territorial_council_election_poll');
        $this->addSql('DROP TABLE territorial_council_official_report_document');
        $this->addSql('DROP TABLE territorial_council');
        $this->addSql('DROP TABLE territorial_council_referent_tag');
        $this->addSql('DROP TABLE territorial_council_zone');
        $this->addSql('DROP TABLE political_committee_quality');
        $this->addSql('DROP TABLE territorial_council_quality');
        $this->addSql('DROP TABLE territorial_council_candidacy_invitation');
        $this->addSql('DROP TABLE territorial_council_candidacy');
        $this->addSql('DROP TABLE institutional_events_categories');
        $this->addSql('DROP TABLE elected_representative_zone');
        $this->addSql('DROP TABLE elected_representative_zone_referent_tag');
        $this->addSql('DROP TABLE elected_representative_zone_parent');
        $this->addSql('DROP TABLE elected_representative_political_function');
        $this->addSql('DROP TABLE elected_representative_social_network_link');
        $this->addSql('DROP TABLE elected_representative_zone_category');
        $this->addSql('DROP TABLE elected_representative_mandate');
        $this->addSql('DROP TABLE elected_representative_user_list_definition_history');
        $this->addSql('DROP TABLE elected_representative_sponsorship');
        $this->addSql('DROP TABLE elected_representative_label');
        $this->addSql('DROP TABLE elected_representative');
        $this->addSql('DROP TABLE elected_representative_user_list_definition');
        $this->addSql('DROP TABLE newsletter_subscriptions');
        $this->addSql('DROP TABLE clarifications');
        $this->addSql('DROP TABLE ton_macron_friend_invitations');
        $this->addSql('DROP TABLE ton_macron_friend_invitation_has_choices');
        $this->addSql('DROP TABLE adherent_reset_password_tokens');
        $this->addSql('DROP TABLE facebook_videos');
        $this->addSql('DROP TABLE committees');
        $this->addSql('DROP TABLE committee_referent_tag');
        $this->addSql('DROP TABLE committee_zone');
        $this->addSql('DROP TABLE custom_search_results');
        $this->addSql('DROP TABLE algolia_candidature');
        $this->addSql('DROP TABLE donation_tags');
        $this->addSql('DROP TABLE turnkey_projects');
        $this->addSql('DROP TABLE turnkey_project_turnkey_project_file');
        $this->addSql('DROP TABLE application_request_volunteer');
        $this->addSql('DROP TABLE volunteer_request_technical_skill');
        $this->addSql('DROP TABLE volunteer_request_theme');
        $this->addSql('DROP TABLE volunteer_request_application_request_tag');
        $this->addSql('DROP TABLE volunteer_request_referent_tag');
        $this->addSql('DROP TABLE application_request_tag');
        $this->addSql('DROP TABLE application_request_technical_skill');
        $this->addSql('DROP TABLE application_request_theme');
        $this->addSql('DROP TABLE application_request_running_mate');
        $this->addSql('DROP TABLE running_mate_request_theme');
        $this->addSql('DROP TABLE running_mate_request_application_request_tag');
        $this->addSql('DROP TABLE running_mate_request_referent_tag');
        $this->addSql('DROP TABLE committee_candidacy_invitation');
        $this->addSql('DROP TABLE emails');
        $this->addSql('DROP TABLE epci');
        $this->addSql('DROP TABLE citizen_project_committee_supports');
        $this->addSql('DROP TABLE chez_vous_measures');
        $this->addSql('DROP TABLE chez_vous_regions');
        $this->addSql('DROP TABLE chez_vous_markers');
        $this->addSql('DROP TABLE chez_vous_measure_types');
        $this->addSql('DROP TABLE chez_vous_cities');
        $this->addSql('DROP TABLE chez_vous_departments');
        $this->addSql('DROP TABLE procuration_requests');
        $this->addSql('DROP TABLE procuration_requests_to_election_rounds');
        $this->addSql('DROP TABLE citizen_action_categories');
        $this->addSql('DROP TABLE programmatic_foundation_measure');
        $this->addSql('DROP TABLE programmatic_foundation_measure_tag');
        $this->addSql('DROP TABLE programmatic_foundation_tag');
        $this->addSql('DROP TABLE programmatic_foundation_approach');
        $this->addSql('DROP TABLE programmatic_foundation_project');
        $this->addSql('DROP TABLE programmatic_foundation_project_tag');
        $this->addSql('DROP TABLE programmatic_foundation_sub_approach');
        $this->addSql('DROP TABLE web_hooks');
        $this->addSql('DROP TABLE proposals');
        $this->addSql('DROP TABLE proposal_proposal_theme');
        $this->addSql('DROP TABLE order_articles');
        $this->addSql('DROP TABLE order_section_order_article');
        $this->addSql('DROP TABLE turnkey_projects_files');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE article_proposal_theme');
        $this->addSql('DROP TABLE social_share_categories');
        $this->addSql('DROP TABLE mailchimp_segment');
        $this->addSql('DROP TABLE unregistrations');
        $this->addSql('DROP TABLE unregistration_referent_tag');
        $this->addSql('DROP TABLE republican_silence');
        $this->addSql('DROP TABLE republican_silence_referent_tag');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP TABLE geo_data');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE failed_login_attempt');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE newsletter_invitations');
        $this->addSql('DROP TABLE member_summary_mission_types');
        $this->addSql('DROP TABLE member_summary_job_experiences');
        $this->addSql('DROP TABLE member_summary_trainings');
        $this->addSql('DROP TABLE member_summary_languages');
        $this->addSql('DROP TABLE vote_result');
        $this->addSql('DROP TABLE election_city_manager');
        $this->addSql('DROP TABLE election_city_candidate');
        $this->addSql('DROP TABLE vote_result_list');
        $this->addSql('DROP TABLE election_city_contact');
        $this->addSql('DROP TABLE election_city_card');
        $this->addSql('DROP TABLE vote_result_list_collection');
        $this->addSql('DROP TABLE ministry_list_total_result');
        $this->addSql('DROP TABLE election_city_partner');
        $this->addSql('DROP TABLE election_city_prevision');
        $this->addSql('DROP TABLE ministry_vote_result');
        $this->addSql('DROP TABLE list_total_result');
        $this->addSql('DROP TABLE elected_representatives_register');
        $this->addSql('DROP TABLE devices');
    }
}
