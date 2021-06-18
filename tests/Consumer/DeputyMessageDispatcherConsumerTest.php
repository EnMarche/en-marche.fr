<?php

namespace Tests\App\Consumer;

use App\Consumer\DeputyMessageDispatcherConsumer;
use App\Entity\Adherent;
use App\Entity\DeputyManagedUsersMessage;
use App\Entity\District;
use App\Mailer\MailerService;
use App\Repository\AdherentRepository;
use App\Repository\DeputyManagedUsersMessageRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeputyMessageDispatcherConsumerTest extends TestCase
{
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

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this
            ->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class))
        ;

        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager = null;
        $this->validator = null;
    }

    public function testDoExecuteWithMessageToSendWithChunk()
    {
        $uuid = 'aze-aze';
        $messageContent = ['uuid' => $uuid];

        $deputyMessage = $this->createConfiguredMock(DeputyManagedUsersMessage::class, [
            'getFrom' => $this->createConfiguredMock(Adherent::class, [
                'getEmailAddress' => 'hello@world.com',
                'getManagedDistrict' => $this->createMock(District::class),
            ]),
            'getUuid' => $this->createConfiguredMock(UuidInterface::class, ['toString' => $uuid]),
        ]);

        $deputyMessageRepository = $this->createConfiguredMock(DeputyManagedUsersMessageRepository::class, [
            'findOneByUuid' => $deputyMessage,
        ]);

        $recipients = [];
        for ($i = 0; $i < (MailerService::PAYLOAD_MAXSIZE + 10); ++$i) {
            $recipients[$i][] = $this->createConfiguredMock(Adherent::class, [
                'getEmailAddress' => uniqid().'@tld.com',
                'getFirstName' => 'Adherent '.$i,
                'getFullName' => 'Adherent '.$i,
            ]);
        }
        $iterableResult = $this->createIterator($recipients);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects($this->once())->method('createDispatcherIteratorForDistrict')->willReturn($iterableResult);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $consumer = new DeputyMessageDispatcherConsumer($this->validator, $this->entityManager);
        $consumer->setAdherentRepository($adherentRepository);
        $consumer->setDeputyManagedUsersMessageRepository($deputyMessageRepository);
        $consumer->setMailer($this->createConfiguredMock(MailerService::class, ['sendMessage' => true]));

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode($messageContent);

        $this->expectOutputString(
            $uuid.' | Dispatching message from hello@world.com'.\PHP_EOL
            .$uuid.' | Message from hello@world.com dispatched (50)'.\PHP_EOL
            .$uuid.' | Message from hello@world.com dispatched (60)'.\PHP_EOL
        );
        $this->assertSame(ConsumerInterface::MSG_ACK, $consumer->execute($message));
    }

    private function createIterator(array $data): IterableResult
    {
        $mock = $this
            ->getMockBuilder(IterableResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['rewind', 'current', 'key', 'next', 'valid', 'count'])
            ->getMock()
        ;
        $iteratorData = new \stdClass();
        $iteratorData->array = $data;
        $iteratorData->position = 0;

        $mock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        ++$iteratorData->position;
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return \count($iteratorData->array);
                    }
                )
            )
        ;

        return $mock;
    }
}
