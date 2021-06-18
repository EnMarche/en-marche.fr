<?php

namespace Tests\App\Membership;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\ActivityPositions;
use App\Membership\AdherentFactory;
use App\Membership\MembershipRequest;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/**
 * @group membership
 */
class AdherentFactoryTest extends TestCase
{
    public function testCreateNonFrenchAdherentFromArray()
    {
        $factory = $this->createFactory();

        $adherent = $factory->createFromArray([
            'password' => 'secret!12345',
            'email' => 'michelle.dufour@example.ch',
            'gender' => 'female',
            'first_name' => 'Michelle',
            'last_name' => 'Dufour',
            'address' => PostAddress::createForeignAddress('CH', '1206', 'Geneva', "39 Rue de l'Athénée"),
            'birthdate' => '1972-11-23',
        ]);

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertNull($adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertSame(['ROLE_USER', 'ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertNull($adherent->eraseCredentials());
        $this->assertSame('michelle.dufour@example.ch', $adherent->getUsername());
        $this->assertSame('secret!12345', $adherent->getPassword());
        $this->assertSame('female', $adherent->getGender());
        $this->assertSame('Michelle', $adherent->getFirstName());
        $this->assertSame('Dufour', $adherent->getLastName());
        $this->assertSame('CH', $adherent->getCountry());
        $this->assertSame("39 Rue de l'Athénée", $adherent->getAddress());
        $this->assertSame('1206', $adherent->getPostalCode());
        $this->assertNull($adherent->getCity());
        $this->assertSame('Geneva', $adherent->getCityName());
        $this->assertEquals(new \DateTime('1972-11-23'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::EMPLOYED, $adherent->getPosition());
        $this->assertFalse($adherent->isEnabled());
    }

    public function testCreateFrenchAdherentFromArray()
    {
        $factory = $this->createFactory();

        $adherent = $factory->createFromArray([
            'password' => '12345!secret',
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182'),
            'birthdate' => '1950-07-08',
            'position' => ActivityPositions::RETIRED,
            'phone' => '+330102030405',
        ]);

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertInstanceOf(PhoneNumber::class, $adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertSame(['ROLE_USER', 'ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertNull($adherent->eraseCredentials());
        $this->assertSame('carl999@example.fr', $adherent->getUsername());
        $this->assertSame('12345!secret', $adherent->getPassword());
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('Carl', $adherent->getFirstName());
        $this->assertSame('Mirabeau', $adherent->getLastName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('122 rue de Mouxy', $adherent->getAddress());
        $this->assertSame('73100', $adherent->getPostalCode());
        $this->assertSame('73100-73182', $adherent->getCity());
        $this->assertEquals(new \DateTime('1950-07-08'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::RETIRED, $adherent->getPosition());
        $this->assertFalse($adherent->isEnabled());
    }

    public function testCreateFrenchAdherentFromMembershipRequest()
    {
        $address = new Address();
        $address->setAddress('122 rue de Mouxy');
        $address->setPostalCode('73100');
        $address->setCountry('FR');
        $address->setCity('73100-73182');

        $request = new MembershipRequest();
        $request->password = 'secret$secret';
        $request->setEmailAddress('carl999@example.fr');
        $request->firstName = 'Carl';
        $request->lastName = 'Mirabeau';
        $request->setAddress($address);
        $request->position = ActivityPositions::RETIRED;
        $request->setBirthdate(new \DateTime('1950-07-08'));
        $request->gender = 'male';

        $factory = $this->createFactory();
        $adherent = $factory->createFromMembershipRequest($request);

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertNull($adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertSame(['ROLE_USER'], $adherent->getRoles());
        $this->assertNull($adherent->eraseCredentials());
        $this->assertSame('carl999@example.fr', $adherent->getUsername());
        $this->assertSame('secret$secret', $adherent->getPassword());
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('Carl', $adherent->getFirstName());
        $this->assertSame('Mirabeau', $adherent->getLastName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('122 rue de Mouxy', $adherent->getAddress());
        $this->assertSame('73100', $adherent->getPostalCode());
        $this->assertSame('73100-73182', $adherent->getCity());
        $this->assertFalse($adherent->isEnabled());
        $this->assertEquals($request->getBirthdate(), $adherent->getBirthdate());
        $this->assertNotSame($request->getBirthdate(), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::RETIRED, $adherent->getPosition());
    }

    private function createFactory(): AdherentFactory
    {
        return new AdherentFactory(
            new EncoderFactory([
                Adherent::class => new PlaintextPasswordEncoder(),
            ])
        );
    }
}
