<?php

namespace Tests\Unit\Module\ComponentRenderer;

use App\Module\ComponentRenderer\ComponentIdGenerator;
use App\Module\ComponentRenderer\RequestToRenderCollector;
use Tests\Unit\TestCase;

class RequestToRenderCollectorTest extends TestCase
{
    public function testAddRequestToRender(): void
    {
        $requestToRenderCollector = new RequestToRenderCollector(new ComponentIdGenerator());

        $this->assertFalse($requestToRenderCollector->hasRequestsToRender());

        $expectedComponentName = 'ComponentName';
        $expectedProperties = ['filed' => 'some value'];

        $componentId = $requestToRenderCollector->addRequestToRender($expectedComponentName, $expectedProperties);

        $requestToRender = $requestToRenderCollector->getRequestsToRender()->getIterator()->current();

        $this->assertTrue($requestToRenderCollector->hasRequestsToRender());
        $this->assertEquals($componentId, $requestToRender->getId());
        $this->assertEquals($expectedComponentName, $requestToRender->getComponentName());
        $this->assertEquals($expectedProperties, $requestToRender->getProperties());
    }
}
