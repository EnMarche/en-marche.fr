<?php

namespace Tests\App\Mailer\Transport;

use App\Mailer\Exception\MailerException;
use App\Mailer\Transport\ApiTransport;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Psr7\Response as HttpResponse;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\App\Test\Mailer\DummyEmailClient;
use Tests\App\Test\Mailer\DummyEmailTemplate;

class ApiTransportTest extends TestCase
{
    public function testCannotSendTemplateEmail()
    {
        $this->expectException(MailerException::class);
        $this->expectExceptionMessage('Unable to send email to recipients.');
        $httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $httpClient->expects($this->once())->method('request')->willReturn(new HttpResponse(400));

        $client = new DummyEmailClient($httpClient);
        $transport = new ApiTransport($client);
        $transport->sendTemplateEmail($this->createDummyEmail());
    }

    public function testSendTemplateEmail()
    {
        $email = $this->createDummyEmail();

        $body = <<<'EOF'
{
    "Sent": [
        {
            "Email": "john.smith@example.tld",
            "MessageID": 111111111111111
        }
    ]
}
EOF;

        $httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $httpClient
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'send', [
                'body' => json_encode($email->getBody()),
            ])
            ->willReturn(new HttpResponse(200, [], $body))
        ;

        $client = new DummyEmailClient($httpClient);
        $transport = new ApiTransport($client);
        $transport->sendTemplateEmail($email);

        $this->assertSame($body, $email->getHttpResponsePayload());
    }

    private function createDummyEmail(): DummyEmailTemplate
    {
        $email = new DummyEmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        return $email;
    }
}
