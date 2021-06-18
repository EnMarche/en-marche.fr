<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCauseEventData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadCoalitionEventData;
use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\OAuth\Model\GrantTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class SubscribeAsAdherentControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    /** @dataProvider provideEvents */
    public function testConnectedUserCanSubscribeAndUnsubscribeOnEvent(
        string $eventUuid,
        string $userEmail,
        string $messageClass
    ) {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_11_UUID,
            'Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ',
            GrantTypeEnum::PASSWORD,
            null,
            $userEmail,
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, sprintf('/api/v3/events/%s/subscribe', $eventUuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertCountMails(1, $messageClass, $userEmail);

        $this->client->request(Request::METHOD_POST, sprintf('/api/v3/events/%s/subscribe', $eventUuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertStatusCode(400, $this->client);

        $this->client->request(Request::METHOD_DELETE, sprintf('/api/v3/events/%s/subscribe', $eventUuid), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->isSuccessful($this->client->getResponse());
    }

    public function provideEvents(): iterable
    {
        yield [LoadCoalitionEventData::EVENT_1_UUID, 'simple-user@example.ch', 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCauseEventData::EVENT_1_UUID, 'simple-user@example.ch', 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCommitteeEventData::EVENT_1_UUID, 'simple-user@example.ch', 'EventRegistrationConfirmationMessage'];
        yield [LoadCoalitionEventData::EVENT_1_UUID, 'carl999@example.fr', 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCauseEventData::EVENT_1_UUID, 'carl999@example.fr', 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCommitteeEventData::EVENT_1_UUID, 'carl999@example.fr', 'EventRegistrationConfirmationMessage'];
    }
}
