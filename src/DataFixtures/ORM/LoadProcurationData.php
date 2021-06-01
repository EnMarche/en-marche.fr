<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Utils\PhoneNumberUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadProcurationData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $presidentialElections = $this->getReference('elections-presidential');
        $legislativeElections = $this->getReference('elections-legislative');
        $partialLegislativeElections = $this->getReference('elections-partial-legislative');

        $manager->persist($this->createRequest(
            'male',
            'Timothé, Jean, Marcel',
            'Baumé',
            'timothe.baume@example.gb',
            '100 Roy Square, Main Street',
            'E14 8BY',
            null,
            'London',
            'GB',
            '+447911123487',
            '1972-11-23',
            'GB',
            'E14 8BY',
            null,
            'London',
            'Lycée international Winston Churchill',
            $presidentialElections->getRounds()
        ));

        $manager->persist($this->createRequest(
            'female',
            'Carine, Margaux',
            'Édouard',
            'caroline.edouard@example.fr',
            '165 rue Marcadet',
            '75018',
            '75018-75118',
            null,
            'FR',
            '+33655443322',
            '1968-10-09',
            'FR',
            '75018',
            '75018-75118',
            null,
            'Damremont',
            array_merge($presidentialElections->getRounds()->toArray(), $legislativeElections->getRounds()->toArray()),
            ProcurationRequest::REASON_RESIDENCY
        ));

        $manager->persist($this->createRequest(
            'female',
            'Fleur',
            'Paré',
            'FleurPare@armyspy.com',
            '13, rue Reine Elisabeth',
            '77000',
            '77000-77288',
            null,
            'FR',
            '+33169641061',
            '1945-01-29',
            'FR',
            '75018',
            '75018-75118',
            null,
            'Aquarius',
            [$presidentialElections->getRounds()->last(), $legislativeElections->getRounds()->last()],
            ProcurationRequest::REASON_HEALTH
        ));

        $manager->persist($this->createRequest(
            'male',
            'Kevin',
            'Delcroix',
            'kevin.delcroix@example.fr',
            '165 rue Marcadet',
            '75018',
            '75018-75118',
            null,
            'FR',
            '+33988776655',
            '1991-01-18',
            'FR',
            '92110',
            '92110-92024',
            null,
            'Mairie',
            [$presidentialElections->getRounds()->last(), $legislativeElections->getRounds()->last()],
            ProcurationRequest::REASON_HELP
        ));

        $manager->persist($this->createRequest(
            'male',
            'Thomas, Jean',
            'René',
            'thomas.rene@example.gb',
            '95, Faubourg Saint Honoré',
            '75008',
            '75008-75108',
            null,
            'FR',
            '+330099887766',
            '1962-10-11',
            'FR',
            '75020',
            '75008-75108',
            null,
            'Lycée Faubourg',
            $partialLegislativeElections->getRounds(),
            ProcurationRequest::REASON_HEALTH
        ));

        $manager->persist($this->createRequest(
            'female',
            'Belle, Carole',
            'D\'Aubigné',
            'belle.carole@example.fr',
            '77, Place de la Madeleine',
            '75010',
            '75010-75110',
            null,
            'FR',
            '+33655443322',
            '1978-03-09',
            'FR',
            '75010',
            '75010-75110',
            null,
            'Madeleine',
            [$partialLegislativeElections->getRounds()->last()],
            ProcurationRequest::REASON_HEALTH
        ));

        $manager->persist($request1 = $this->createRequest(
            'male',
            'William',
            'Brunelle',
            'WilliamBrunelle@dayrep.com',
            '59, Avenue De Marlioz',
            '44000',
            '44000-44109',
            null,
            'FR',
            '+33411809703',
            '1964-01-16',
            'FR',
            '44000',
            '44000-44109',
            null,
            'Saighterse',
            array_merge($presidentialElections->getRounds()->toArray(), $legislativeElections->getRounds()->toArray()),
            ProcurationRequest::REASON_HEALTH
        ));

        $manager->persist($request2 = $this->createRequest(
            'female',
            'Alice',
            'Delavega',
            'alice.delavega@exemple.org',
            '12, Avenue de la République',
            '75011',
            '75011-75111',
            null,
            'FR',
            '+33111809703',
            '1984-11-05',
            'FR',
            '75008',
            '75008-75108',
            null,
            'École de la république',
            $partialLegislativeElections->getRounds(),
            ProcurationRequest::REASON_TRAINING
        ));

        $manager->persist($request3 = $this->createRequest(
            'male',
            'Jean',
            'Dell',
            'jean.dell@example.gb',
            '100 Roy Square, Main Street',
            'E14 8BY',
            'London',
            'London',
            'GB',
            '+447911123443',
            '1972-11-23',
            'FR',
            '75008',
            '75008-75108',
            null,
            'Gymnase de Iéna',
            $partialLegislativeElections->getRounds(),
            ProcurationRequest::REASON_RESIDENCY,
            false
        ));

        $manager->persist($this->createRequest(
            'female',
            'Aurélie',
            'Baumé',
            'aurelie.baume@example.gb',
            '24, Carrer de Pelai',
            '08001',
            'Barcelona',
            'Barcelona',
            'ES',
            '+349999888222',
            '1985-01-20',
            'ES',
            '08001',
            'Barcelona',
            'Barcelona',
            'Institut Català de la Salut',
            $partialLegislativeElections->getRounds()
        ));

        $manager->persist($this->createRequest(
            'male',
            'René',
            'Rage',
            'rene.rage@example.gb',
            '100 Roy Square, Main Street',
            'E14 8BY',
            null,
            'London',
            'GB',
            '+447911123456',
            '1972-11-23',
            'GB',
            'E14 8BY',
            null,
            'London',
            'Lycée international Winston Churchill',
            $partialLegislativeElections->getRounds()
        ));

        $manager->persist($this->createRequest(
            'male',
            'Jean-Michel',
            'Amoitié',
            'jeanmichel.amoitié@example.es',
            '4Q Covent Garden',
            'GV6H',
            'London',
            null,
            'GB',
            '+447911123457',
            '1989-12-20',
            'GB',
            'GV6H',
            'London',
            null,
            'Camden',
            $partialLegislativeElections->getRounds(),
            ProcurationRequest::REASON_HEALTH,
            0
        ));

        $manager->persist($this->createRequest(
            'male',
            'Jean-Michel',
            'Gastro',
            'jeanmichel.gastro@example.es',
            '4Q Covent Garden',
            'GV6H',
            'London',
            null,
            'GB',
            '+447911123458',
            '1989-12-20',
            'GB',
            'GV6H',
            'London',
            null,
            'Camden',
            $partialLegislativeElections->getRounds(),
            ProcurationRequest::REASON_HEALTH,
            0
        ));

        $manager->persist($this->createProxyProposal(
            'male',
            'Maxime',
            'Michaux',
            'maxime.michaux@example.fr',
            '14 rue Jules Ferry',
            '75018',
            '75020-75120',
            null,
            'FR',
            '+33988776655',
            '1989-02-17',
            'FR',
            '75018',
            '75018-75118',
            null,
            'Mairie',
            [$presidentialElections->getRounds()->last(), $legislativeElections->getRounds()->last()]
        ));

        $manager->persist($this->createProxyProposal(
            'male',
            'Jean-Michel',
            'Carbonneau',
            'jm.carbonneau@example.fr',
            '14 rue Jules Ferry',
            '75018',
            '75020-75120',
            null,
            'FR',
            '+33988776655',
            '1974-01-17',
            'FR',
            '75018',
            '75018-75118',
            null,
            'Lycée général Zola',
            array_merge($presidentialElections->getRounds()->toArray(), $legislativeElections->getRounds()->toArray())
        ));

        $manager->persist($proxy1 = $this->createProxyProposal(
            'male',
            'Benjamin',
            'Robitaille',
            'BenjaminRobitaille@teleworm.us',
            '47, place Stanislas',
            '44100',
            '44100-44109',
            null,
            'FR',
            '+33269692256',
            '1969-10-17',
            'FR',
            '44100',
            '44100-44109',
            null,
            'Bentapair',
            array_merge($presidentialElections->getRounds()->toArray(), $legislativeElections->getRounds()->toArray()),
            ProcurationProxy::RELIABILITY_ADHERENT,
            'Responsable procuration'
        ));

        $manager->persist($proxy2 = $this->createProxyProposal(
            'male',
            'Romain',
            'Gentil',
            'romain.gentil@exemple.org',
            '2, place Iéna',
            '75008',
            '75008-75108',
            null,
            'FR',
            '+33673849284',
            '1979-12-01',
            'FR',
            '75008',
            '75008-75108',
            null,
            'Gymnase de Iéna',
            array_merge(
                $presidentialElections->getRounds()->toArray(),
                $legislativeElections->getRounds()->toArray(),
                $partialLegislativeElections->getRounds()->toArray()
            ),
            ProcurationProxy::RELIABILITY_ADHERENT,
            'Responsable procuration',
            2
        ));

        $manager->persist($this->createProxyProposal(
            'female',
            'Léa',
            'Bouquet',
            'lea.bouquet@exemple.org',
            '18, avenue de la République',
            '75010',
            '75010-75110',
            null,
            'FR',
            '+33673839259',
            '1982-02-21',
            'FR',
            '75010',
            '75010-75110',
            null,
            'École de la République',
            [$partialLegislativeElections->getRounds()->first()],
            ProcurationProxy::RELIABILITY_ADHERENT,
            'Responsable procuration'
        ));

        $manager->persist($this->createProxyProposal(
            'male',
            'Emmanuel',
            'Harquin',
            'emmanuel.harquin@exemple.org',
            '53, Quai des Belges',
            '91300',
            '91300-91377',
            null,
            'FR',
            '+33675645342',
            '1953-09-19',
            'FR',
            '91300',
            '91300-91377',
            null,
            'École 42',
            $partialLegislativeElections->getRounds(),
            ProcurationProxy::RELIABILITY_ADHERENT,
            'Responsable procuration'
        ));

        $manager->persist($this->createProxyProposal(
            'female',
            'Annie',
            'Versaire',
            'annie.versaire@exemple.org',
            '100 Roy Square, Main Street',
            'E14 8BY',
            null,
            'London',
            'GB',
            '+447911123459',
            '1972-11-23',
            'GB',
            'E14 8BY',
            null,
            'London',
            'Lycée international Winston Churchill',
            $partialLegislativeElections->getRounds(),
            ProcurationProxy::RELIABILITY_ADHERENT,
            'Désactivé',
            1,
            1
        ));

        $manager->persist($this->createProxyProposal(
            'male',
            'Jean-Marc',
            'Gastro',
            'jeanmarc.gastro@example.es',
            '4Q Covent Garden',
            'GV6H',
            'London',
            null,
            'GB',
            '+447911123465',
            '1989-12-21',
            'GB',
            'GV6H',
            'London',
            null,
            'Camden',
            $partialLegislativeElections->getRounds(),
            ProcurationProxy::RELIABILITY_ADHERENT,
            'Responsable procuration',
            3
        ));

        $manager->flush();

        $manager->refresh($request1);
        $manager->refresh($request2);
        $manager->refresh($request3);
        $manager->refresh($proxy1);
        $manager->refresh($proxy2);

        $finder = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_4_UUID);

        $request1->process($proxy1, $finder);
        $request2->process($proxy2, $finder);
        $request3->process($proxy2, $finder);

        $reflectionClass = new \ReflectionClass(ProcurationRequest::class);
        $reflectionProperty = $reflectionClass->getProperty('processedAt');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($request1, new \DateTime('-48 hours'));
        $reflectionProperty->setValue($request2, new \DateTime('-72 hours'));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadElectionData::class,
        ];
    }

    private function createRequest(
        string $gender,
        string $firstNames,
        string $lastName,
        string $email,
        string $address,
        ?string $postalCode,
        ?string $city,
        ?string $cityName,
        ?string $country,
        ?string $phone,
        string $birthDate,
        string $voteCountry,
        ?string $votePostalCode,
        ?string $voteCity,
        ?string $voteCityName,
        string $voteOffice,
        iterable $electionRounds,
        string $reason = ProcurationRequest::REASON_HOLIDAYS,
        bool $requestFromFrance = true
    ): ProcurationRequest {
        if ($phone) {
            $phone = PhoneNumberUtils::create($phone);
        }

        $request = new ProcurationRequest();
        $request->setGender($gender);
        $request->setFirstNames($firstNames);
        $request->setLastName($lastName);
        $request->setEmailAddress($email);
        $request->setAddress($address);
        $request->setPostalCode($postalCode);
        $request->setCity($city);
        $request->setCityName($cityName);
        $request->setCountry($country);
        $request->setPhone($phone);
        $request->setBirthdate(new \DateTime($birthDate));
        $request->setVoteCountry($voteCountry);
        $request->setVotePostalCode($votePostalCode);
        $request->setVoteCity($voteCity);
        $request->setVoteCityName($voteCityName);
        $request->setVoteOffice($voteOffice);
        $request->setElectionRounds($electionRounds);
        $request->setReason($reason);
        $request->setRequestFromFrance($requestFromFrance);

        return $request;
    }

    public function createProxyProposal(
        string $gender,
        string $firstNames,
        string $lastName,
        string $email,
        string $address,
        ?string $postalCode,
        ?string $city,
        ?string $cityName,
        ?string $country,
        ?string $phone,
        string $birthDate,
        string $voteCountry,
        ?string $votePostalCode,
        ?string $voteCity,
        ?string $voteCityName,
        string $voteOffice,
        iterable $electionRounds,
        int $reliability = ProcurationProxy::RELIABILITY_UNKNOWN,
        string $reliabilityDescription = '',
        int $proxiesCount = 1,
        bool $disabled = false
    ): ProcurationProxy {
        if ($phone) {
            $phone = PhoneNumberUtils::create($phone);
        }

        $proxy = new ProcurationProxy();
        $proxy->setGender($gender);
        $proxy->setFirstNames($firstNames);
        $proxy->setLastName($lastName);
        $proxy->setEmailAddress($email);
        $proxy->setAddress($address);
        $proxy->setPostalCode($postalCode);
        $proxy->setCity($city);
        $proxy->setCityName($cityName);
        $proxy->setCountry($country);
        $proxy->setPhone($phone);
        $proxy->setBirthdate(new \DateTime($birthDate));
        $proxy->setVoteCountry($voteCountry);
        $proxy->setVotePostalCode($votePostalCode);
        $proxy->setVoteCity($voteCity);
        $proxy->setVoteCityName($voteCityName);
        $proxy->setVoteOffice($voteOffice);
        $proxy->setElectionRounds($electionRounds);
        $proxy->setReliability($reliability);
        $proxy->setReliabilityDescription($reliabilityDescription);
        $proxy->setDisabled($disabled);
        $proxy->setProxiesCount($proxiesCount);

        return $proxy;
    }
}
