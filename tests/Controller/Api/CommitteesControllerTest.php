<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadCommitteeData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

/**
 * @group functional
 * @group api
 */
class CommitteesControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testApiApprovedCommittees()
    {
        $this->client->request(Request::METHOD_GET, '/api/committees');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertGreaterThanOrEqual(8, \count(\GuzzleHttp\json_decode($content, true)));
        $this->assertEachJsonItemContainsKey('uuid', $content);
        $this->assertEachJsonItemContainsKey('slug', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('position', $content);
    }

    public function testGetCommitteeCandidacyReturnsNothingIfNonMemberOrAnonymous(): void
    {
        $url = sprintf('/api/committees/%s/candidacies', LoadCommitteeData::COMMITTEE_4_UUID);

        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'michelle.dufour@example.ch');

        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testGetCommitteeCandidaciesReturnGoodJson(): void
    {
        $this->authenticateAsAdherent($this->client, 'assesseur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/api/committees/%s/candidacies', LoadCommitteeData::COMMITTEE_6_UUID));
        $this->isSuccessful($response = $this->client->getResponse());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('metadata', $data);

        self::assertSame([
            'total' => 2,
            'males' => 2,
            'females' => 0,
        ], $data['metadata']);

        $this->assertArrayHasKey('candidacies', $data);

        self::assertCount(2, $data['candidacies']);

        PHPUnitHelper::assertArraySubset([
            'gender' => 'male',
            'first_name' => 'Bob',
            'last_name' => 'Assesseur',
        ], $data['candidacies'][0]);

        PHPUnitHelper::assertArraySubset([
            'photo' => null,
            'gender' => 'male',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
        ], $data['candidacies'][1]);
    }
}
