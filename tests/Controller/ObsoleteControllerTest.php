<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class ObsoleteControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideActions
     */
    public function testActions(string $path, bool $permanent = false)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode($permanent ? Response::HTTP_GONE : Response::HTTP_NOT_FOUND, $this->client);
    }

    public function provideActions(): \Generator
    {
        yield ['/emmanuel-macron/desintox'];
        yield ['/emmanuel-macron/desintox/heritier-hollande-traite-quiquennat'];
    }
}
