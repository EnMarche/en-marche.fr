<?php

namespace App\Command;

use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\IdeaNotificationDates;
use App\Mailer\MailerService;
use App\Mailer\Message\IdeaFinalizeNotificationMessage;
use App\Mailer\Message\IdeaFinalizePreNotificationMessage;
use App\Repository\IdeasWorkshop\IdeaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotifyIdeaAuthorCommand extends Command
{
    protected static $defaultName = 'idea-workshop:notification:idea-author';

    private $entityManager;
    private $ideaRepository;
    private $urlGenerator;
    private $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        IdeaRepository $ideaRepository,
        UrlGeneratorInterface $urlGenerator,
        MailerService $transactionalMailer
    ) {
        $this->entityManager = $entityManager;
        $this->ideaRepository = $ideaRepository;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $transactionalMailer;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('caution', null, InputOption::VALUE_NONE, 'Send an email 3 days before the note finished date');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ideaNotificationDates = $this->findIdeaNotificationDates();

        [$startDate, $endDate] = $this->prepareDates($ideaNotificationDates, $isCautionMode = $input->getOption('caution'));

        foreach ($this->getIdeas($startDate, $endDate) as $idea) {
            $this->sendMail($idea, $isCautionMode);
        }

        $this->saveLastDate($ideaNotificationDates, $endDate, $isCautionMode);
    }

    /**
     * @return Idea[]
     */
    private function getIdeas(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->ideaRepository->createQueryBuilder('idea')
            ->where('idea.finalizedAt IS NOT NULL')
            ->andWhere(':start_date <= idea.finalizedAt AND idea.finalizedAt < :end_date')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery()
            ->getResult()
        ;
    }

    private function prepareDates(IdeaNotificationDates $ideaNotificationDates, bool $isCautionMode): array
    {
        $endDate = new \DateTimeImmutable($isCautionMode ? '+3 days' : 'now');

        $startDate = $isCautionMode ? $ideaNotificationDates->getCautionLastDate() : $ideaNotificationDates->getLastDate();

        if (!$startDate) {
            $startDate = $endDate->modify('-1 hours');
        }

        return [
            $startDate,
            $endDate,
        ];
    }

    private function findIdeaNotificationDates(): IdeaNotificationDates
    {
        $ideasNotificationDates = $this
            ->entityManager
            ->getRepository(IdeaNotificationDates::class)
            ->findAll()
        ;

        if (!$ideasNotificationDates) {
            return new IdeaNotificationDates();
        }

        return reset($ideasNotificationDates);
    }

    private function sendMail(Idea $idea, bool $isCautionMode): void
    {
        if ($isCautionMode) {
            $message = IdeaFinalizePreNotificationMessage::create(
                $idea->getAuthor(),
                $this->urlGenerator->generate(
                    'react_app_ideas_workshop_proposition',
                    ['id' => $idea->getUuidAsString()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        } else {
            $message = IdeaFinalizeNotificationMessage::create(
                $idea->getAuthor(),
                $this->urlGenerator->generate(
                    'react_app_ideas_workshop_proposition',
                    ['id' => $idea->getUuidAsString()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        $this->mailer->sendMessage($message);
    }

    private function saveLastDate(
        IdeaNotificationDates $ideaNotificationDates,
        \DateTimeInterface $lastDate,
        bool $isCautionMode
    ): void {
        if ($isCautionMode) {
            $ideaNotificationDates->setCautionLastDate($lastDate);
        } else {
            $ideaNotificationDates->setLastDate($lastDate);
        }

        if (!$ideaNotificationDates->getId()) {
            $this->entityManager->persist($ideaNotificationDates);
        }

        $this->entityManager->flush();
    }
}
