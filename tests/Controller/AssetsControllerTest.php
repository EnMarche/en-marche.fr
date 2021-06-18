<?php

namespace Tests\App\Controller;

use League\Glide\Signatures\Signature;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;

/**
 * @group functional
 * @group controller
 */
class AssetsControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /** @var Signature */
    private $signature;

    public function testAssetWithSignatureIsFound()
    {
        $this->client->request(Request::METHOD_GET, '/assets/10decembre.jpg', [
            's' => $this->signature->generateSignature('/assets/10decembre.jpg', []),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testAssetWithoutSignatureIsNotFound()
    {
        $this->client->request(Request::METHOD_GET, '/assets/10decembre.jpg');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testInvalidAssetWithSignatureIsNotFound()
    {
        $this->client->request(Request::METHOD_GET, '/assets/invalid.jpg', [
            's' => $this->signature->generateSignature('/assets/invalid.jpg', []),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testStaticMaps()
    {
        $this->client->request(Request::METHOD_GET, '/maps/47.3950813,8.3361425');
        $response = $this->client->getResponse();

        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $weight = \strlen($response->getContent());

        $this->assertGreaterThan(1024, $weight, 'We are assuming that an image map should be greater than 1 KB');
        $this->assertLessThan(1024 * 1024, $weight, 'We are assuming that an image map should be less than 1 MB');
        $this->assertSame('image/png', $response->headers->get('content-type'));

        $tag = md5($response->getContent());

        $this->client->request(Request::METHOD_GET, '/maps/47.3950813,8.3361425');

        $this->assertEquals(
            $tag,
            md5($this->client->getResponse()->getContent()),
            'Exactly same queries should have exactly same image data responses'
        );

        $this->client->request(Request::METHOD_GET, '/maps/-47.3950813,-8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_OK,
            $this->client->getResponse(),
            'Negative values are allowed'
        );

        $this->client->request(Request::METHOD_GET, '/maps/-47.39508,-8.3361');

        $this->assertResponseStatusCode(
            Response::HTTP_OK,
            $this->client->getResponse(),
            'Precision of various digits count is allowed'
        );

        $this->client->request(Request::METHOD_GET, '/maps/-47,-8');

        $this->assertResponseStatusCode(
            Response::HTTP_OK,
            $this->client->getResponse(),
            'Integer values are allowed'
        );
    }

    public function testStaticMapsWithWrongQuery()
    {
        $this->client->request(Request::METHOD_GET, '/maps/47.3950813');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Query should have complete coordinates parameters'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.,8.');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Coordinates should have a precision of at least 1 digit'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.39508135,8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Coordinates should have a precision of max 7 digits'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.3950813;8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Coordinates separator should be a comma'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47,3950813,8,3361425');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Precision separator should be a dot'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.39508a3,8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Foreign characters are not allowed'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->signature = SignatureFactory::create($this->getParameter('kernel.secret'));
    }

    protected function tearDown(): void
    {
        $this->signature = null;

        parent::tearDown();
    }
}
