<?php

namespace Tests\Unit\Module\MailCheck;

use App\Module\MailCheck\Exceptions\MailCheckAiException;
use App\Module\MailCheck\MailCheckAiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Unit\TestCase;

class MailCheckAiClientTest extends TestCase
{
    /**
     * @dataProvider validDomainDataProvider
     */
    public function testCheckDomainWithValidResponses(string $domain, int $statusCode, string $body, bool $expectedDisposableStatus): void
    {
        $mailCheckAiClient = $this->createMailCheckAiClientMock(new Response($statusCode, [], $body));

        $isDisposable = $mailCheckAiClient->isDisposableDomain($domain);

        $this->assertEquals($expectedDisposableStatus, $isDisposable);
    }

    /**
     * @dataProvider invalidDomainDataProvider
     */
    public function testCheckDomainWithInvalidResponses(string $domain, int $statusCode, string $body, string $expectedExceptionMessage): void
    {
        $mailCheckAiClient = $this->createMailCheckAiClientMock(new Response($statusCode, [], $body));

        $this->expectException(MailCheckAiException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $mailCheckAiClient->isDisposableDomain($domain);
    }

    private function createMailCheckAiClientMock(Response $response): MailCheckAiClient
    {
        $mockHandler = new MockHandler([$response]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        return new MailCheckAiClient($httpClient);
    }

    /**
     * @return array[][]
     */
    public function validDomainDataProvider(): array
    {
        return [
            ['notDisposable.com', 200, '{"status": 200, "disposable": false}', false],
            ['disposable.com', 200, '{"status": 200, "disposable": true}', true],
        ];
    }

    /**
     * @return array[][]
     */
    public function invalidDomainDataProvider(): array
    {
        return [
            ['badDomain.com', 400, '{"status": 400, "error": "The domain is invalid."}', 'The domain is invalid.'],
            ['rateLimit.com', 429, '{"status": 429, "error": "Rate limit is exceeded."}', 'Rate limit is exceeded.'],
        ];
    }
}
