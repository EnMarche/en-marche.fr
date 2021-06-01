<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateManagedUsersByReferentCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    protected function configure()
    {
        $this
            ->setName('app:referent:populate')
            ->setDescription('Create managed users by referent from the datas of concerned tables.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqlFromCommitteeMembership = <<<'SQL'
          INSERT INTO projection_managed_users
            (status, type, original_id, email, postal_code, city, country, first_name, last_name, age, phone, 
            committees, is_committee_member, is_committee_host, is_committee_supervisor, committee_postal_code, 
            subscribed_tags, subscription_types, created_at, gender, interests, supervisor_tags, citizen_projects, 
            citizen_projects_organizer)
            SELECT
            0,
            'adherent',
            a.id,
            a.email_address,
            a.address_postal_code,
            a.address_city_name,
            a.address_country,
            a.first_name,
            a.last_name,
            EXTRACT(YEAR FROM AGE(a.birthdate, NOW())) AS age,
            a.phone,
            (
                SELECT STRING_AGG(c.name, '|')
                FROM committees_memberships cm
                LEFT JOIN committees c ON cm.committee_id = c.id
                WHERE cm.adherent_id = a.id
            ),
            (
                SELECT COUNT(cm.id) > 0
                FROM committees_memberships cm
                LEFT JOIN committees c ON cm.committee_id = c.id
                WHERE cm.adherent_id = a.id AND c.status = 'APPROVED'
            ),
            (
                SELECT COUNT(cm.id) > 0
                FROM committees_memberships cm
                LEFT JOIN committees c ON cm.committee_id = c.id
                WHERE cm.adherent_id = a.id AND c.status = 'APPROVED' AND cm.privilege = 'HOST'
            ),
            (
                SELECT COUNT(cm.id) > 0
                FROM committees_memberships cm
                LEFT JOIN committees c ON cm.committee_id = c.id
                WHERE cm.adherent_id = a.id AND c.status = 'APPROVED' AND cm.privilege = 'SUPERVISOR'
            ),
            (
                SELECT c.address_postal_code
                FROM committees c
                JOIN committees_memberships cm ON c.id = cm.committee_id
                WHERE cm.adherent_id = a.id AND c.status = 'APPROVED' AND cm.privilege = 'SUPERVISOR'
                LIMIT 1
            ),
            (
                SELECT STRING_AGG(tag.code, ',')
                FROM referent_tags tag
                INNER JOIN adherent_referent_tag adherent_tag ON adherent_tag.referent_tag_id = tag.id
                WHERE adherent_tag.adherent_id = a.id
                GROUP BY a.id
            ),
            (
                SELECT STRING_AGG(st.code, ',')
                FROM subscription_type st
                JOIN adherent_subscription_type ast ON ast.subscription_type_id = st.id
                WHERE ast.adherent_id = a.id
                GROUP BY a.id
            ),
            a.registered_at,
            a.gender,
            a.interests,
            (
                SELECT STRING_AGG(rt.code, ',')
                FROM committees c
                INNER JOIN committees_memberships cm ON cm.committee_id = c.id
                INNER JOIN committee_referent_tag crt ON crt.committee_id = c.id
                INNER JOIN referent_tags rt ON rt.id = crt.referent_tag_id
                WHERE cm.adherent_id = a.id AND cm.privilege = "SUPERVISOR"
            ),
            (
                SELECT
                    CAST(
                        CONCAT(
                            '{',
                            STRING_AGG(
                                CONCAT(
                                    JSON_QUOTE(cp.slug),
                                    ':',
                                    JSON_QUOTE(cp.name)
                                ),
                                ','
                            ),
                            '}'
                        )
                    AS JSON)
                FROM citizen_project_memberships cpm
                INNER JOIN citizen_projects cp ON cpm.citizen_project_id = cp.id
                WHERE cpm.adherent_id = a.id AND cpm.privilege = "FOLLOWER" AND cp.status = "APPROVED"
            ),
            (
                SELECT
                    CAST(
                        CONCAT(
                            '{',
                            STRING_AGG(
                                CONCAT(
                                    JSON_QUOTE(cp.slug),
                                    ':',
                                    JSON_QUOTE(cp.name)
                                ),
                                ','
                            ),
                            '}'
                        )
                    AS JSON)
                FROM citizen_project_memberships cpm
                INNER JOIN citizen_projects cp ON cpm.citizen_project_id = cp.id
                WHERE cpm.adherent_id = a.id AND cpm.privilege = "HOST" AND cp.status = "APPROVED"
            )
            FROM adherents a
SQL;

        $sqlStatus = 'UPDATE projection_managed_users SET status = status + 1';
        $sqlDeleteOld = 'DELETE FROM projection_managed_users WHERE status >= 2';

        try {
            $stmt = $this->manager->getConnection()->prepare($sqlFromCommitteeMembership);
            $stmt->execute();
            $stmt = $this->manager->getConnection()->prepare($sqlStatus);
            $stmt->execute();
            $stmt = $this->manager->getConnection()->prepare($sqlDeleteOld);
            $stmt->execute();

            $output->writeln('Creation of managed users by referent was successfully completed');
        } catch (\Exception $e) {
            $output->writeln('The error occurred during execution : '.$e->getMessage());
        }
    }

    /** @required */
    public function setManager(EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }
}
