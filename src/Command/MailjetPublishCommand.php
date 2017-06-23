<?php

namespace AppBundle\Command;

use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailjetPublishCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:mailjet:publish')
            ->addArgument('client', InputArgument::REQUIRED, 'campaign or transactional')
            ->addArgument('uuid', InputArgument::REQUIRED)
            ->setDescription('Publish a message in RabbitMQ for the given UUID to redeliver the e-mail')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $input->getArgument('client');
        if (!in_array($client, ['campaign', 'transactional'], true)) {
            throw new \InvalidArgumentException('Invalid client type');
        }

        $uuid = $input->getArgument('uuid');
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException('Invalid UUID');
        }

        $producer = $this->getContainer()->get('old_sound_rabbit_mq.mailjet_'.$client.'_producer');
        $producer->publish(json_encode(['uuid' => $uuid]));
    }
}
