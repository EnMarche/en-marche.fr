<?php

namespace Tests\App;

use App\Committee\Feed\CommitteeFeedManager;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\Administrator;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use App\Entity\Coalition\Coalition;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Entity\CommitteeMembership;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\DonatorIdentifier;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Email;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\EventCategory;
use App\Entity\Event\EventRegistration;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\Filesystem\File;
use App\Entity\Invite;
use App\Entity\JeMarcheReport;
use App\Entity\MyEuropeChoice;
use App\Entity\MyEuropeInvitation;
use App\Entity\NewsletterInvite;
use App\Entity\NewsletterSubscription;
use App\Entity\PostAddress;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Entity\ReferentSpaceAccessInformation;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\RepublicanSilence;
use App\Entity\SubscriptionType;
use App\Entity\Summary;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\Entity\TonMacronChoice;
use App\Entity\TonMacronFriendInvitation;
use App\Entity\Transaction;
use App\Entity\UserListDefinition;
use App\Membership\ActivityPositions;
use App\Repository\AdherentActivationTokenRepository;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentRepository;
use App\Repository\AdherentResetPasswordTokenRepository;
use App\Repository\AdministratorRepository;
use App\Repository\Coalition\CauseFollowerRepository;
use App\Repository\Coalition\CauseRepository;
use App\Repository\Coalition\CoalitionRepository;
use App\Repository\CommitteeFeedItemRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\Repository\DonationRepository;
use App\Repository\DonatorIdentifierRepository;
use App\Repository\DonatorRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Repository\EmailRepository;
use App\Repository\EmailSubscriptionHistoryRepository;
use App\Repository\EventRegistrationRepository;
use App\Repository\EventRepository;
use App\Repository\Filesystem\FileRepository;
use App\Repository\InstitutionalEventRepository;
use App\Repository\InviteRepository;
use App\Repository\JeMarcheReportRepository;
use App\Repository\MyEuropeChoiceRepository;
use App\Repository\MyEuropeInvitationRepository;
use App\Repository\NewsletterInviteRepository;
use App\Repository\NewsletterSubscriptionRepository;
use App\Repository\ProcurationProxyRepository;
use App\Repository\ProcurationRequestRepository;
use App\Repository\ReferentSpaceAccessInformationRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Repository\SummaryRepository;
use App\Repository\TerritorialCouncil\OfficialReportRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeFeedItemRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilFeedItemRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\Repository\TonMacronChoiceRepository;
use App\Repository\TonMacronFriendInvitationRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserListDefinitionRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\EntityRepository;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Glide\Server;
use libphonenumber\PhoneNumber;
use Symfony\Bridge\Doctrine\ManagerRegistry;

trait TestHelperTrait
{
    /** @var Adherent[] */
    protected $adherents;

    public function get(string $id): ?object
    {
        return self::$container->get($id);
    }

    public function getParameter(string $name)
    {
        return self::$container->getParameter($name);
    }

    public function assertMailCountRecipients(int $count, ?Email $mail): void
    {
        $this->assertNotNull($mail);
        $this->assertCount($count, $mail->getRecipients());
    }

    public function assertCountMails(int $count, string $type, ?string $recipient = null): void
    {
        $repository = $this->getEmailRepository();

        if ($recipient) {
            $this->assertCount($count, $repository->findRecipientMessages($type, $recipient));

            return;
        }

        $this->assertCount($count, $repository->findMessages($type));
    }

    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->get('doctrine');
    }

    public function getStorage(): Filesystem
    {
        return $this->get(FilesystemInterface::class);
    }

    public function getGlide(): Server
    {
        return $this->get(Server::class);
    }

    public function getEntityManager(string $class = null): ObjectManager
    {
        if (null === $class) {
            return $this->getManagerRegistry()->getManager();
        }

        return $this->getManagerRegistry()->getManagerForClass($class);
    }

    public function getRepository(string $class): ObjectRepository
    {
        return $this->getManagerRegistry()->getRepository($class);
    }

    public function getActivationTokenRepository(): AdherentActivationTokenRepository
    {
        return $this->getRepository(AdherentActivationToken::class);
    }

    public function getResetPasswordTokenRepository(): AdherentResetPasswordTokenRepository
    {
        return $this->getRepository(AdherentResetPasswordToken::class);
    }

    public function getAdherentRepository(): AdherentRepository
    {
        return $this->getRepository(Adherent::class);
    }

    protected function getAdministratorRepository(): AdministratorRepository
    {
        return $this->getRepository(Administrator::class);
    }

    public function getCommitteeRepository(): CommitteeRepository
    {
        return $this->getRepository(Committee::class);
    }

    public function getEventRepository(): EventRepository
    {
        return $this->getRepository(CommitteeEvent::class);
    }

    public function getInstitutionalEventRepository(): InstitutionalEventRepository
    {
        return $this->getRepository(InstitutionalEvent::class);
    }

    public function getCommitteeFeedItemRepository(): CommitteeFeedItemRepository
    {
        return $this->getRepository(CommitteeFeedItem::class);
    }

    public function getCommitteeMembershipRepository(): CommitteeMembershipRepository
    {
        return $this->getRepository(CommitteeMembership::class);
    }

    public function getCommitteeMembershipHistoryRepository(): EntityRepository
    {
        return $this->getRepository(CommitteeMembershipHistory::class);
    }

    public function getEventRegistrationRepository(): EventRegistrationRepository
    {
        return $this->getRepository(EventRegistration::class);
    }

    public function getDonationRepository(): DonationRepository
    {
        return $this->getRepository(Donation::class);
    }

    public function getDonatorRepository(): DonatorRepository
    {
        return $this->getRepository(Donator::class);
    }

    public function getDonatorIdentifierRepository(): DonatorIdentifierRepository
    {
        return $this->getRepository(DonatorIdentifier::class);
    }

    public function getTransactionRepository(): TransactionRepository
    {
        return $this->getRepository(Transaction::class);
    }

    public function getInvitationRepository(): InviteRepository
    {
        return $this->getRepository(Invite::class);
    }

    public function getNewsletterInvitationRepository(): NewsletterInviteRepository
    {
        return $this->getRepository(NewsletterInvite::class);
    }

    public function getNewsletterSubscriptionRepository(): NewsletterSubscriptionRepository
    {
        return $this->getRepository(NewsletterSubscription::class);
    }

    public function getJeMarcheReportRepository(): JeMarcheReportRepository
    {
        return $this->getRepository(JeMarcheReport::class);
    }

    public function getProcurationRequestRepository(): ProcurationRequestRepository
    {
        return $this->getRepository(ProcurationRequest::class);
    }

    public function getProcurationProxyRepository(): ProcurationProxyRepository
    {
        return $this->getRepository(ProcurationProxy::class);
    }

    public function getEmailRepository(): EmailRepository
    {
        return $this->getRepository(Email::class);
    }

    public function getSummaryRepository(): SummaryRepository
    {
        return $this->getRepository(Summary::class);
    }

    public function getTonMacronChoiceRepository(): TonMacronChoiceRepository
    {
        return $this->getRepository(TonMacronChoice::class);
    }

    public function getTonMacronInvitationRepository(): TonMacronFriendInvitationRepository
    {
        return $this->getRepository(TonMacronFriendInvitation::class);
    }

    public function getMyEuropeChoiceRepository(): MyEuropeChoiceRepository
    {
        return $this->getRepository(MyEuropeChoice::class);
    }

    public function getMyEuropeInvitationRepository(): MyEuropeInvitationRepository
    {
        return $this->getRepository(MyEuropeInvitation::class);
    }

    public function getEmailSubscriptionHistoryRepository(): EmailSubscriptionHistoryRepository
    {
        return $this->getRepository(EmailSubscriptionHistory::class);
    }

    public function getSubscriptionTypeRepository(): SubscriptionTypeRepository
    {
        return $this->getRepository(SubscriptionType::class);
    }

    public function getReferentSpaceAccessInformationRepository(): ReferentSpaceAccessInformationRepository
    {
        return $this->getRepository(ReferentSpaceAccessInformation::class);
    }

    public function getUserListDefinitionRepository(): UserListDefinitionRepository
    {
        return $this->getRepository(UserListDefinition::class);
    }

    public function getElectedRepresentativeRepository(): ElectedRepresentativeRepository
    {
        return $this->getRepository(ElectedRepresentative::class);
    }

    public function getTerritorialCouncilRepository(): TerritorialCouncilRepository
    {
        return $this->getRepository(TerritorialCouncil::class);
    }

    public function getPoliticalCommitteeRepository(): PoliticalCommitteeRepository
    {
        return $this->getRepository(PoliticalCommittee::class);
    }

    public function getTerritorialCouncilFeedItemRepository(): TerritorialCouncilFeedItemRepository
    {
        return $this->getRepository(TerritorialCouncilFeedItem::class);
    }

    public function getPoliticalCommitteeFeedItemRepository(): PoliticalCommitteeFeedItemRepository
    {
        return $this->getRepository(PoliticalCommitteeFeedItem::class);
    }

    public function getOfficialReportRepository(): OfficialReportRepository
    {
        return $this->getRepository(OfficialReport::class);
    }

    public function getFileRepository(): FileRepository
    {
        return $this->getRepository(File::class);
    }

    public function getCommitteeFeedManager(): CommitteeFeedManager
    {
        return $this->get(CommitteeFeedManager::class);
    }

    protected function getAdherent(string $uuid): ?Adherent
    {
        return $this->getAdherentRepository()->findByUuid($uuid);
    }

    protected function getCommittee(string $uuid): ?Committee
    {
        return $this->getCommitteeRepository()->findOneByUuid($uuid);
    }

    public function getCommitteeMandateRepository(): CommitteeAdherentMandateRepository
    {
        return $this->getRepository(CommitteeAdherentMandate::class);
    }

    public function getCauseRepository(): CauseRepository
    {
        return $this->getRepository(Cause::class);
    }

    public function getCauseFollowerRepository(): CauseFollowerRepository
    {
        return $this->getRepository(CauseFollower::class);
    }

    public function getCoalitionRepository(): CoalitionRepository
    {
        return $this->getRepository(Coalition::class);
    }

    /**
     * @param bool $refresh Leave to false to avoid reloading from database
     *
     * @return Adherent[]
     */
    protected function getAdherents($refresh = false): array
    {
        if (null === $this->adherents || $refresh) {
            $this->adherents = [
                $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID),
                $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID),
            ];
        }

        return $this->adherents;
    }

    /**
     * @param string|null $email E-mail used to generate a unique UUID
     */
    protected function createAdherent(string $email = null): Adherent
    {
        $email = $email ?: 'john.smith@example.org';
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return Adherent::create(
            Adherent::createUuid($email),
            $email,
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::STUDENT,
            PostAddress::createFrenchAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );
    }

    protected function disableRepublicanSilence(): void
    {
        $this
            ->getRepository(RepublicanSilence::class)
            ->createQueryBuilder('r')
            ->delete()
            ->getQuery()
            ->execute()
        ;
    }

    protected function getEventCategoryIdForName(string $categoryName): int
    {
        return $this->manager->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName])->getId();
    }
}
