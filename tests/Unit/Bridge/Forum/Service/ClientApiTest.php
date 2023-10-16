<?php

namespace Tests\Unit\Bridge\Forum\Service;

use App\Bridge\Xenforo\Provider\Api\ClientApi;
use App\Service\ClientIp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\TestCase;

class ClientApiTest extends TestCase
{
    public function testSuccessRequest()
    {
        $array = [
            'status' => true,
            'count' => 1,
        ];

        $client = new ClientApi(
              $this->getClientMock(json_encode($array)),
              $this->getUuidMock(),
              $this->getClientIpMock()
        );

        $this->assertSame($array, $client->handle('/api/'));
    }

    public function testFailedRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Return incorrect answer');

        $client = new ClientApi(
            $this->getClientMock('failed response'),
            $this->getUuidMock(),
            $this->getClientIpMock()
        );
        $client->handle('/api/');
    }

    public function testFailedException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error message');

        $client = new ClientApi(
            $this->getClientMock('', true),
            $this->getUuidMock(),
            $this->getClientIpMock()
        );

        $client->handle('/api/');
    }

    /**
     * Password must be not empty
     */
    public function testFailedExceptionWithErrors()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Username must be not empty');

        $client = new ClientApi(
            $this->getClientMock(json_encode(['errors' => ['Username must be not empty', 'Password must be not empty']]), true, true),
            $this->getUuidMock(),
            $this->getClientIpMock()
        );

        $client->handle('/api/');
    }

    private function getClientMock($requestReturn, bool $failed = false, bool $exceptionWithResponse = false): Client
    {
        $stream = $this->createMock(Stream::class);
        $stream
            ->expects(!$failed || $exceptionWithResponse ? $this->once() : $this->never())
            ->method('getContents')
            ->willReturn($requestReturn)
        ;

        $response = $this->createMock(Response::class);
        $response
            ->expects(!$failed || $exceptionWithResponse ? $this->once() : $this->never())
            ->method('getBody')
            ->willReturn($stream)
        ;

        $client = $this->createMock(Client::class);
        if (!$failed) {
            $client->expects($this->once())
                ->method('request')
                ->willReturnCallback(function ($method, $uri, $arguments) use ($response) {
                    $this->assertEquals('POST', $method);
                    $this->assertEquals('resolventa/api/', $uri);
                    $this->assertArrayHasKey('multipart', $arguments);

                    $this->assertArrayHasKey('headers', $arguments);
                    $this->assertArrayHasKey('Resolventa-X-Forwarded-For', $arguments['headers']);
                    $this->assertEquals($this->getClientIpMock()->getIp(), $arguments['headers']['Resolventa-X-Forwarded-For']);

                    foreach ($arguments['multipart'] as $argument) {
                        $this->assertArrayHasKey('name', $argument);
                        $this->assertArrayHasKey('contents', $argument);
                    }

                    return $response;
                });
        } else {
            $client
                ->expects($this->once())
                ->method('request')
                ->will($this->throwException(
                    !$exceptionWithResponse ? new \Exception('Error message', 400) : new ClientException('Error message', $this->createMock(Request::class), $response)
                ))
            ;
        }

        return $client;
    }

    private function getUuidMock(): UuidFactoryInterface
    {
        $uuid = $this->createMock(UuidInterface::class);
        $uuid
            ->expects($this->once())
            ->method('toString')
            ->willReturn('string')
        ;

        $mock = $this->createMock(UuidFactoryInterface::class);
        $mock
            ->expects($this->once())
            ->method('uuid4')
            ->willReturn($uuid)
        ;

        return $mock;
    }

    private function getClientIpMock(): ClientIp
    {
        $mock = $this->createMock(ClientIp::class);
        $mock
            ->expects($this->once())
            ->method('getIp')
            ->willReturn('127.0.0.1')
        ;

        return $mock;
    }
}
