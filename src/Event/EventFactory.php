<?php

namespace AppBundle\Event;

use AppBundle\Address\Address;
use AppBundle\Address\PostAddressFactory;
use AppBundle\CitizenAction\CitizenActionCommand;
use AppBundle\Entity\Event;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\PostAddress;
use Ramsey\Uuid\Uuid;

class EventFactory
{
    private $addressFactory;

    public function __construct(PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): Event
    {
        foreach (['uuid', 'committee', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at', 'capacity'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        return new Event(
            $uuid,
            $data['organizer'] ?? null,
            $data['committee'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at'],
            $data['capacity'],
            $data['is_for_legislatives'] ?? false
        );
    }

    public function createCitizenActionFromArray(array $data): CitizenAction
    {
        foreach (['uuid', 'organizer', 'citizen_project', 'name', 'category', 'description', 'address', 'begin_at', 'finish_at'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing.', $key));
            }
        }

        $uuid = Uuid::fromString($data['uuid']);

        return new CitizenAction(
            $uuid,
            $data['organizer'],
            $data['citizen_project'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['address'],
            $data['begin_at'],
            $data['finish_at']
        );
    }

    public function createFromEventCommand(EventCommand $command): Event
    {
        return new Event(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getCommittee(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt()->format(DATE_ATOM),
            $command->getFinishAt()->format(DATE_ATOM),
            $command->getCapacity(),
            $command->isForLegislatives()
        );
    }

    public function updateFromEventCommand(Event $event, EventCommand $command): Event
    {
        $event->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt()->format(DATE_ATOM),
            $command->getFinishAt()->format(DATE_ATOM),
            $command->getCapacity(),
            $command->isForLegislatives()
        );

        return $event;
    }

    public function createFromCitizenActionCommand(CitizenActionCommand $command): CitizenAction
    {
        return new CitizenAction(
            $command->getUuid(),
            $command->getAuthor(),
            $command->getCitizenProject(),
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt()
        );
    }

    public function updateFromCitizenActionCommand(CitizenActionCommand $command, CitizenAction $action): void
    {
        $action->update(
            $command->getName(),
            $command->getCategory(),
            $command->getDescription(),
            $this->createPostAddress($command->getAddress()),
            $command->getBeginAt(),
            $command->getFinishAt()
        );
    }

    private function createPostAddress(Address $address): PostAddress
    {
        return $this->addressFactory->createFromAddress($address);
    }
}
