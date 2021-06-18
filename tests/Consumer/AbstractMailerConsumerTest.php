<?php

namespace Tests\App\Consumer;

use App\Entity\Email;
use App\Exception\InvalidUuidException;
use App\Mailer\EmailClientInterface;
use App\Mailer\Exception\MailerException;
use App\Repository\EmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ConnectException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractMailerConsumerTest extends TestCase
{
    public const CLASS_NAME = 'App\Consumer\AbstractMailerConsumer';

    /**
     * @var MockObject|ValidatorInterface
     */
    private $validator;

    /**
     * @var MockObject|EntityManagerInterface
     */
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager = null;
        $this->validator = null;
    }

    public function testDoExecuteWithoutUuidInMessage()
    {
        $uuid = 'aze-aze';

        $emailRepository = $this->createMock(EmailRepository::class);
        $emailRepository->expects($this->once())->method('findOneByUuid')->willReturn(null);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getEmailRepository'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())->method('error')->with('Email not found', ['uuid' => $uuid]);
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->once())->method('getEmailRepository')->willReturn($emailRepository);

        $this->expectOutputString($uuid.' | Email not found, rejecting'.\PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testDoExecuteWithInvalidUuidFormatInMessage()
    {
        $uuid = 'aze-aze';
        $invalidUuidException = $this
            ->getMockBuilder(InvalidUuidException::class)
            ->setConstructorArgs([
                'message' => sprintf('Uuid "%s" is not valid.', $uuid),
            ])
            ->getMock()
        ;

        $emailRepository = $this->createMock(EmailRepository::class);
        $emailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willThrowException($invalidUuidException)
        ;

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getEmailRepository'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $this->setOutputCallback(function () {
        });

        $logger
            ->expects($this->once())
            ->method('error')
            ->with('UUID is invalid format', ['exception' => $invalidUuidException])
        ;
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->once())->method('getEmailRepository')->willReturn($emailRepository);

        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testDoExecuteNotDelivery()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePayload = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePayload);

        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson'])
            ->getMock()
        ;

        $email
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %s recipients', $messageClass, $sender, \count($recipients)))
        ;

        $email
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded)
        ;

        $emailRepository = $this->createMock(EmailRepository::class);
        $emailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($email)
        ;

        $emailClient = $this->createMock(EmailClientInterface::class);
        $emailClient
            ->expects($this->once())
            ->method('sendEmail')
            ->willReturn('')
            ->with($messagePayloadEncoded)
        ;

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getEmailRepository', 'getEmailClient'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);

        $abstractConsumer->expects($this->once())->method('getEmailRepository')->willReturn($emailRepository);
        $abstractConsumer->expects($this->once())->method('getEmailClient')->willReturn($emailClient);
        $this->expectOutputString($uuid.' | Delivering '.sprintf('%s from %s to %s recipients', $messageClass, $sender, \count($recipients)).\PHP_EOL.$uuid.' | An issue occured, requeuing'.\PHP_EOL);

        $this->assertSame(ConsumerInterface::MSG_REJECT_REQUEUE, $abstractConsumer->execute($message));
    }

    public function testDoExecuteSuccessDelivery()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePayload = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePayload);

        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson', 'delivered'])
            ->getMock()
        ;

        $email
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %i recipients', $messageClass, $sender, \count($recipients)))
        ;

        $email
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded)
        ;

        $emailRepository = $this->createMock(EmailRepository::class);
        $emailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($email)
        ;

        $emailRepository
            ->expects($this->once())
            ->method('setDelivered')
            ->with($email, 'OK')
        ;

        $emailClient = $this->createMock(EmailClientInterface::class);
        $emailClient
            ->expects($this->once())
            ->method('sendEmail')
            ->willReturn('OK')
            ->with($messagePayloadEncoded)
        ;

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getEmailRepository', 'getEmailClient', 'getLogger'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);

        $abstractConsumer->expects($this->any())->method('getLogger')->willReturn($this->createMock(LoggerInterface::class));
        $abstractConsumer->expects($this->any())->method('getEmailRepository')->willReturn($emailRepository);
        $abstractConsumer->expects($this->once())->method('getEmailClient')->willReturn($emailClient);
        $this->expectOutputString($uuid.' | Delivering '.sprintf('%s from %s to %i recipients', $messageClass, $sender, \count($recipients)).\PHP_EOL);

        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testDoExecuteMailerException()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePayload = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePayload);

        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson', 'delivered'])
            ->getMock()
        ;

        $email
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %i recipients', $messageClass, $sender, \count($recipients)))
        ;

        $email
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded)
        ;

        $emailRepository = $this->createMock(EmailRepository::class);
        $emailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($email)
        ;

        $mailerException = $this
            ->getMockBuilder(MailerException::class)
            ->setConstructorArgs([
                'message' => 'Unable to send email to recipients.',
            ])
            ->getMock()
        ;

        $emailClient = $this->createMock(EmailClientInterface::class);
        $emailClient
            ->expects($this->once())
            ->method('sendEmail')
            ->with($messagePayloadEncoded)
            ->willThrowException($mailerException)
        ;

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getEmailRepository', 'getEmailClient'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $this->setOutputCallback(function () {
        });

        $logger->expects($this->once())->method('error')->with('Unable to send email to recipients.', ['exception' => $mailerException]);
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->any())->method('getEmailRepository')->willReturn($emailRepository);
        $abstractConsumer->expects($this->once())->method('getEmailClient')->willReturn($emailClient);
        $this->assertSame(ConsumerInterface::MSG_REJECT_REQUEUE, $abstractConsumer->execute($message));
    }

    public function testDoExecuteGuzzleException()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePayload = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePayload);

        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson', 'delivered'])
            ->getMock()
        ;

        $email
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %i recipients', $messageClass, $sender, \count($recipients)))
        ;

        $email
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded)
        ;

        $emailRepository = $this->createMock(EmailRepository::class);
        $emailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($email)
        ;

        $connectException = $this->createMock(ConnectException::class);

        $emailClient = $this->createMock(EmailClientInterface::class);
        $emailClient
            ->expects($this->once())
            ->method('sendEmail')
            ->with($messagePayloadEncoded)
            ->willThrowException($connectException)
        ;

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getEmailRepository', 'getEmailClient'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $this->setOutputCallback(function () {
        });

        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                sprintf(
                    'Error with HTTP connection while sending a mail with UUID %s. (%s)',
                    $uuid,
                    null
                ),
                ['exception' => $connectException]
            )
        ;
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->any())->method('getEmailRepository')->willReturn($emailRepository);
        $abstractConsumer->expects($this->once())->method('getEmailClient')->willReturn($emailClient);
        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }
}
