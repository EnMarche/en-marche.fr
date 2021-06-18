<?php

namespace Tests\App\Consumer;

use App\Consumer\MailerConsumer;
use App\Mailer\MailerService;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailerConsumerTest extends TestCase
{
    private $validator;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createConfiguredMock(EntityManagerInterface::class, [
            'getConnection' => $this->createMock(Connection::class),
        ]);

        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager = null;
        $this->validator = null;
    }

    public function testExecuteWithInvalidMessagePayload(): void
    {
        $invalidData = [
            'fromName' => '',
            'fromEmail' => '',
            'subject' => '',
            'templateKey' => '',
            'recipients' => [],
        ];
        $messageBody = json_encode($invalidData);

        $consumer = new MailerConsumer($this->validator, $this->entityManager);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Message structure is not valid',
                [
                    'message' => $messageBody,
                    'violations' => ['' => [null]],
                ]
            )
        ;
        $consumer->setLogger($logger);

        $constraintViolationList = new ConstraintViolationList([
            $this->createMock(ConstraintViolationInterface::class),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($invalidData)
            ->willReturn($constraintViolationList)
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = $messageBody;

        $this->assertSame(ConsumerInterface::MSG_ACK, $consumer->execute($message));
    }

    public function testExecuteCallSendMessageWithGoodMessageObject(): void
    {
        $data = [
            'fromName' => 'EnMarche',
            'fromEmail' => 'jemarche@en-marche.dev',
            'subject' => 'B0nj0ur!',
            'templateKey' => 123456,
            'recipients' => [[
                'name' => 'Mario Brossolini',
                'email' => 'adherent@en-marche.dev',
            ]],
        ];
        $messageBody = json_encode($data);

        $consumer = new MailerConsumer($this->validator, $this->entityManager);

        $this->validator
            ->expects($this->any())
            ->method('validate')
            ->willReturn(new ConstraintViolationList())
        ;

        $mailerService = $this->createMock(MailerService::class);
        $mailerService
            ->expects($this->once())
            ->method('sendMessage')
        ;

        $consumer->setMailerService($mailerService);

        $message = $this->createMock(AMQPMessage::class);
        $message->body = $messageBody;

        $this->assertSame(ConsumerInterface::MSG_ACK, $consumer->execute($message));
    }
}
