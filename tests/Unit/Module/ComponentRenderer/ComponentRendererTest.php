<?php

namespace Tests\Unit\Module\ComponentRenderer;

use App\Module\ComponentRenderer\ComponentIdGenerator;
use App\Module\ComponentRenderer\ComponentRenderer;
use App\Module\ComponentRenderer\RequestToRenderCollector;
use App\Module\ComponentRenderer\SSRClient;
use App\Module\ComponentRenderer\TransferObject\RenderedComponent;
use Tests\Unit\TestCase;

class ComponentRendererTest extends TestCase
{

    public function testRender(): void
    {
        $componentRenderer = new ComponentRenderer($this->createSSRClientMock([
            new RenderedComponent('some-id-1', '<Component 1/>'),
            new RenderedComponent('some-id-2', '<Component 2/>'),
            new RenderedComponent('some-id-3', '<Component 3/>'),
        ]), $this->createRequestToRenderCollector());

        $htmlContent = 'some-id-1 some-id-2 some-id-3';

        $htmlContentWithRenderedComponents = $componentRenderer->render($htmlContent);

        $this->assertEquals('<Component 1/> <Component 2/> <Component 3/>', $htmlContentWithRenderedComponents);
    }

    private function createSSRClientMock(array $renderedComponents = []): SSRClient
    {
        $ssrClient = $this->createMock(SSRClient::class);

        $ssrClient->method('sendRequestsToRenderComponents')->willReturn($renderedComponents);

        return $ssrClient;
    }

    private function createRequestToRenderCollector(): RequestToRenderCollector
    {
        return new RequestToRenderCollector(new ComponentIdGenerator());
    }
}
