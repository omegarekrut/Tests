<?php

namespace Tests\Unit\Module\ComponentRenderer;

use App\Module\ComponentRenderer\RequestToRenderCollection;
use App\Module\ComponentRenderer\SSRClient;
use App\Module\ComponentRenderer\TransferObject\RequestToRender;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\Unit\LoggerMock;
use Tests\Unit\TestCase;

class SSRClientTest extends TestCase
{
    public function testSendRequestsToRenderComponents(): void
    {
        $expectedResponses = [
            new Response(200, [], 'Example 1'),
            new Response(200, [], 'Example 2'),
        ];

        $requestsToRender = [
            new RequestToRender('id-1', 'SomeComponent', []),
            new RequestToRender('id-2', 'SomeComponent', []),
        ];

        $requestsToRenderCollection = $this->createRequestsToRenderCollection($requestsToRender);

        $httpClient = $this->createHttpClient($expectedResponses);
        $ssrClient = new SSRClient($httpClient, new LoggerMock());

        $renderedComponents = $ssrClient->sendRequestsToRenderComponents($requestsToRenderCollection);

        foreach ($renderedComponents as $i => $renderedComponent) {
            $this->assertEquals($requestsToRender[$i]->getId(), $renderedComponent->getId());
            $this->assertEquals((string) $expectedResponses[$i]->getBody(), $renderedComponent->getHtmlContent());
        }
    }

    public function testSendRequestsToRenderComponentsWithError(): void
    {
        $requestToRender = new RequestToRender('id-1', 'SomeComponent', ['filed' => 'value']);

        $requestsToRenderAsJson = json_encode([
            'id' => $requestToRender->getId(),
            'component' => $requestToRender->getComponentName(),
            'props' => $requestToRender->getProperties(),
        ]);

        $expectedResponses = [
            new RequestException('Some message', new Request('GET', '/', [], $requestsToRenderAsJson)),
        ];

        $requestsToRenderCollection = $this->createRequestsToRenderCollection([$requestToRender]);

        $logger = new LoggerMock();
        $httpClient = $this->createHttpClient($expectedResponses);
        $ssrClient = new SSRClient($httpClient, $logger);

        $renderedComponents = $ssrClient->sendRequestsToRenderComponents($requestsToRenderCollection);

        $logMessages = $logger->getMessages();

        $this->assertCount(1, $renderedComponents);
        $this->assertEquals($renderedComponents[0]->getHtmlContent(), '<!--'.$requestToRender->getId().'-->');
        $this->assertEquals('Request to render component is failed', $logMessages[0]['message']);
        $this->assertEquals($requestsToRenderAsJson, $logMessages[0]['context']['requestBody']);
    }

    /**
     * @param Response[]|RequestException[] $responses
     */
    private function createHttpClient(array $responses): Client
    {
        $mockHandler = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mockHandler);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * @param RequestToRender[] $requestsToRender
     */
    private function createRequestsToRenderCollection(array $requestsToRender): RequestToRenderCollection
    {
        $requestsToRenderCollection = new RequestToRenderCollection();

        foreach ($requestsToRender as $requestToRender) {
            $requestsToRenderCollection->add($requestToRender);
        }

        return $requestsToRenderCollection;
    }
}
