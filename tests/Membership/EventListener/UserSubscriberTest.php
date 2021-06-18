<?php

namespace Tests\App\Membership\EventListener;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\EventListener\UserSubscriber;
use App\Membership\UserEvent;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractKernelTestCase;

class UserSubscriberTest extends AbstractKernelTestCase
{
    public function testSerialiseUser()
    {
        $serializer = $this->get('jms_serializer');

        $producer = $this->getMockBuilder(ProducerInterface::class)->getMock();
        $userSubscriber = new UserSubscriber($producer, $serializer);

        $user = Adherent::create(
            Uuid::fromString('a046adbe-9c7b-56a9-a676-6151a6785dda'),
            'jacques.picard@en-marche.fr',
            'password',
            'male',
            'Jacques',
            'Picard',
            new \DateTime(),
            'foo',
            PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', null, 48.8699464, 2.3297187)
        );

        $this->assertSame(
            '{"uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda","subscriptionExternalIds":[],"city":"Paris 8e","country":"FR","zipCode":"75008","tags":[],"emailAddress":"jacques.picard@en-marche.fr","firstName":"Jacques","lastName":"Picard"}',
            $userSubscriber->serialize(new UserEvent($user))
        );
    }
}
