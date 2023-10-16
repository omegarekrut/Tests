<?php

namespace Tests\Unit\Module\VerifyMail;

use App\Module\VerifyMail\Exceptions\VerifyMailException;
use App\Module\VerifyMail\VerifyMailClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Unit\TestCase;

class VerifyMailClientTest extends TestCase
{
    /**
     * @dataProvider validEmailDataProvider
     */
    public function testCheckEmailWithValidResponses(string $email, int $statusCode, string $body, bool $expectedDisposableStatus): void
    {
        $mailCheckAiClient = $this->createVerifyMailClientMock(new Response($statusCode, [], $body));

        $isDisposable = $mailCheckAiClient->isDisposableEmail($email);

        $this->assertEquals($expectedDisposableStatus, $isDisposable);
    }

    /**
     * @dataProvider invalidEmailDataProvider
     */
    public function testCheckEmailWithInvalidResponses(string $email, int $statusCode, string $body, string $expectedExceptionMessage): void
    {
        $mailCheckAiClient = $this->createVerifyMailClientMock(new Response($statusCode, [], $body));

        $this->expectException(VerifyMailException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $mailCheckAiClient->isDisposableEmail($email);
    }

    private function createVerifyMailClientMock(Response $response): VerifyMailClient
    {
        $mockHandler = new MockHandler([$response]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        return new VerifyMailClient($httpClient);
    }

    /**
     * @return array[][]
     */
    public function validEmailDataProvider(): array
    {
        return [
            ['notDisposable@email.com', 200, '{"status": 200, "disposable": false}', false],
            ['disposable@email.com', 200, '{"status": 200, "disposable": true}', true],
        ];
    }

    /**
     * @return array[][]
     */
    public function invalidEmailDataProvider(): array
    {
        return [
            ['badResponse@email.com', 200, 'status": 400, "error": "The domain is invalid."}', 'Invalid json in response body: status": 400, "error": "The domain is invalid."}'],
            ['badRequest@email.com', 400, '{"error": "some error."}', 'Invalid response status code: 400'],
            ['responseWithoutDisposable@email.com', 200, '{"notDisposableField": "notDisposableValue"}', 'Response body doesnt contain disposable information'],
        ];
    }
}
