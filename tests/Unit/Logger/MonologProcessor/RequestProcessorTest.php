<?php

namespace Tests\Unit\Logger\MonologProcessor;

use App\Logger\MonologProcessor\RequestProcessor;
use App\Service\ClientIp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class RequestProcessorTest extends TestCase
{
    private $recordFixture = [
        'message' => '',
        'extra' => [],
    ];

    private $requestFixture = [
        'message' => "\n*Headers*\nreferer: http://example.com/referer",
        'uri' => 'http://example.com/page',
        'ip' => '127.0.0.42',
        'referer' => 'http://example.com/referer',
    ];

    public function testPrepareRecord(): void
    {
        $request = $this->createSymfonyRequest($this->requestFixture);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $clientIp = $this->createConfiguredMock(
            ClientIp::class,
            ['getIp' => $this->requestFixture['ip']],
        );

        $processor = new RequestProcessor($requestStack, $clientIp);
        $record = $processor->processRecord($this->recordFixture);
        $extraRequest = explode(', ', $record['extra']['Request']);

        $this->assertEquals($this->requestFixture['message'], $record['message']);
        $this->assertEquals('uri: '.$this->requestFixture['uri'], $extraRequest[0]);
        $this->assertEquals('ip: '.$this->requestFixture['ip'], $extraRequest[1]);
        $this->assertEquals(PHP_EOL.'referer: '.$this->requestFixture['referer'], $extraRequest[2]);
    }

    private function createSymfonyRequest(array $requestFixture): Request
    {
        $components = parse_url($requestFixture['uri']);

        return new Request([], [], [], [], [], [
            'REMOTE_ADDR' => $this->requestFixture['ip'],
            'HTTP_REFERER' => $this->requestFixture['referer'],
            'SERVER_NAME' => $components['host'],
            'REQUEST_URI' => $components['path'],
            'SERVER_PORT' => 80,
        ]);
    }
}
