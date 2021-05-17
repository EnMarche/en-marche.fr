<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\Clarification;
use App\Entity\EntityContentInterface;
use App\Entity\Page;
use App\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use Lullabot\AMP\AMP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildAmpContentCommand extends Command
{
    public const ENTITIES_TO_BUILD = [
        Article::class,
        Proposal::class,
        Clarification::class,
        Page::class,
    ];

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var CommonMarkConverter
     */
    private $markdown;

    protected function configure()
    {
        $this
            ->setName('app:amp:build')
            ->setDescription('Build the AMP version of all the content entities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (self::ENTITIES_TO_BUILD as $entity) {
            $output->writeln('Synchronizing entity '.$entity.' ... ');
            $this->buildEntity($output, $entity);
            $output->write("\n");
        }
    }

    private function buildEntity(OutputInterface $output, $className): void
    {
        /** @var EntityContentInterface[] $items */
        $items = $this->manager->getRepository($className)->findAll();

        $progressbar = new ProgressBar($output, ceil(\count($items) / 10));

        foreach ($items as $k => $item) {
            $html = $this->markdown->convertToHtml($item->getContent());

            $amp = new AMP();
            $amp->loadHtml($html);

            $item->setAmpContent($amp->convertToAmpHtml());

            if (0 === $k % 10) {
                $progressbar->advance();
            }
        }

        $progressbar->finish();
        $this->manager->flush();
    }

    /** @required */
    public function setManager(EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    /** @required */
    public function setMarkdown(CommonMarkConverter $markdown): void
    {
        $this->markdown = $markdown;
    }
}
