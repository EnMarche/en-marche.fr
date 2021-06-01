<?php

namespace Tests\App\Controller\Api\UserListDefinition;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\UserListDefinitionEnum;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ElectedRepresentativeUserListDefinitionControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function providePages()
    {
        return [
            ['/api/elected-representative/user-list-definitions/%s/members'],
        ];
    }

    public function provideUsers(): iterable
    {
        yield ['referent@en-marche-dev.fr'];
        yield ['benjyd@aol.com'];
    }

    /**
     * @dataProvider providePages
     */
    public function testForbiddenWithGet($path)
    {
        foreach (UserListDefinitionEnum::TYPES as $type) {
            $this->client->request(Request::METHOD_GET, sprintf($path, $type));

            $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
        }
    }

    /**
     * @dataProvider providePages
     */
    public function testForbiddenAsAnonymous($path)
    {
        foreach (UserListDefinitionEnum::TYPES as $type) {
            $this->client->request(Request::METHOD_POST, sprintf($path, $type), [], [], [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]);

            $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);
        }
    }

    public function testCannotGetUserListDefinitionMembersForTypeWithoutIds()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/elected-representative/user-list-definitions/%s/members', UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testGetUserListDefinitionMembersForType()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/elected-representative/user-list-definitions/%s/members', UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE),
            ['ids' => [2, 3]],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);

        $this->assertCount(2, $data);
        $this->assertArrayHasKey(0, $data);
        $this->assertArraySubset([
            'id' => 2,
            'type' => 'elected_representative',
            'code' => 'instances_member',
            'color' => null,
            'label' => 'Participe aux instances',
            'ids' => ['2'],
        ], $data[0]);
        $this->assertArrayHasKey(1, $data);
        $this->assertSame(1, $data[1]['id']);
        $this->assertSame('elected_representative', $data[1]['type']);
        $this->assertSame('supporting_la_rem', $data[1]['code']);
        $this->assertSame('Sympathisant(e) LaREM', $data[1]['label']);
        $this->assertContains('2', $data[1]['ids']);
        $this->assertContains('3', $data[1]['ids']);
    }

    public function testCannotSaveUserListDefinitionMembersForTypeWithoutMembers()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/elected-representative/user-list-definitions/members/save'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testCannotSaveUserListDefinitionMembersForTypeWithoutRoute()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/elected-representative/user-list-definitions/members/save'),
            ['members' => []],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testSaveUserListDefinitionMembersForType()
    {
        $electedRepresentativeRepository = $this->getElectedRepresentativeRepository();
        $userListDefinitionRepository = $this->getUserListDefinitionRepository();
        /** @var ElectedRepresentative $er2 */
        $er2 = $electedRepresentativeRepository->find(2);
        $er3 = $electedRepresentativeRepository->find(3);
        $supportingLaRem = $userListDefinitionRepository->findOneBy(['code' => UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM]);
        $instancesMember = $userListDefinitionRepository->findOneBy(['code' => UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER]);

        $this->assertTrue($er2->getUserListDefinitions()->contains($supportingLaRem));
        $this->assertTrue($er2->getUserListDefinitions()->contains($instancesMember));
        $this->assertTrue($er3->getUserListDefinitions()->contains($supportingLaRem));
        $this->assertFalse($er3->getUserListDefinitions()->contains($instancesMember));

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(
            Request::METHOD_POST,
            sprintf('/api/elected-representative/user-list-definitions/members/save'),
            [
                'members' => [
                    11 => ['member_of' => [1, 3]],
                    22 => ['not_member_of' => [2]],
                ],
            ],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->disableRepublicanSilence();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
