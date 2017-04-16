<?php

namespace AppBundle\Command;

use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer;
use AlgoliaSearch\Client;
use AppBundle\Entity\Article;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\CustomSearchResult;
use AppBundle\Entity\Proposal;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaSynchronizeCommand extends ContainerAwareCommand
{
    const ENTITIES_TO_INDEX = [
        Article::class,
        Proposal::class,
        Clarification::class,
        CustomSearchResult::class,
    ];

    /**
     * @var string
     */
    private $env;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var ManualIndexer
     */
    private $indexer;

    /**
     * @var Client
     */
    private $client;

    protected function configure()
    {
        $this
            ->setName('app:algolia:synchronize')
            ->addArgument('entityName', InputArgument::OPTIONAL, 'Which type of entity do you want to reindex? If not set, all is assumed.')
            ->setDescription('Synchronize')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $algolia = $this->getContainer()->get('algolia.indexer');

        $this->env = $this->getContainer()->getParameter('kernel.environment');
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->indexer = $algolia->getManualIndexer($this->manager);
        $this->client = $algolia->getClient();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityNameToIndex = $input->getArgument('entityName');
        $toIndex = $entityNameToIndex ? [$this->manager->getRepository($entityNameToIndex)->getClassName()] : self::ENTITIES_TO_INDEX;

        foreach ($toIndex as $entity) {
            $output->write('Synchronizing entity '.$entity.' ... ');
            $nbIndexes = $this->synchronizeEntity($entity);
            $output->writeln('done, '.$nbIndexes.' records indexed');
        }
    }

    private function synchronizeEntity($className)
    {
        return (int) $this->indexer->reIndex($className, [
            'batchSize' => 3000,
            'safe' => true,
            'clearEntityManager' => true,
        ]);
    }
}
