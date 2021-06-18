<?php

namespace Tests\App\Test\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class DummyConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        throw new \LogicException('This consumer should not be called as its purpose is to create a queue to read messages for testing purpose');
    }
}
