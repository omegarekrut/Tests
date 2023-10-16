<?php

namespace Tests\Functional\Module\Pagination\Handler;

use App\Module\PaginationRouting\EventSubscriber\PaginationFactoryConfiguratorSubscriber;
use App\Module\PaginationRouting\KnpPaginationFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Tests\Functional\Module\Pagination\Mock\PaginationController;
use Tests\Functional\TestCase;

class PaginationFactoryConfiguratorSubscriberTest extends TestCase
{
    private $arguments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->arguments = new ParameterBag([]);
    }

    protected function tearDown(): void
    {
        unset($this->arguments);

        parent::tearDown();
    }

    public function testCallForEventWithoutController(): void
    {
        $expectedEvent = $event = $this->getFilterControllerArgumentsEvent(null);

        $handler = $this->getContainer()->get(PaginationFactoryConfiguratorSubscriber::class);
        $handler->onKernelControllerArguments($event);

        $this->assertEquals($expectedEvent, $event);
    }

    /**
     * @annotation
     */
    public function testNotSupportedAction(): void
    {
        $expectedEvent = $event = $this->getFilterControllerArgumentsEvent([
            new PaginationController,
            'actionWithoutAnnotation',
        ]);

        $handler = $this->getContainer()->get(PaginationFactoryConfiguratorSubscriber::class);
        $handler->onKernelControllerArguments($event);

        $this->assertEquals($expectedEvent, $event);
    }

    public function testWithoutRequiredMethodArgument(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Exist annotation without App\Module\PaginationRouting\KnpPaginationFactory argument.');

        $event = $this->getFilterControllerArgumentsEvent([
            new PaginationController,
            'actionWithAnnotation',
        ]);

        $handler = $this->getContainer()->get(PaginationFactoryConfiguratorSubscriber::class);
        $handler->onKernelControllerArguments($event);
    }

    public function testRewriteMethodArgument(): void
    {
        $this->arguments->replace([$this->getContainer()->get(KnpPaginationFactory::class)]);

        $event = $this->getFilterControllerArgumentsEvent([
            new PaginationController,
            'actionWithAnnotation',
        ]);

        $handler = $this->getContainer()->get(PaginationFactoryConfiguratorSubscriber::class);
        $handler->onKernelControllerArguments($event);

        $paginationFactory = $this->arguments->get(0);

        $this->assertInstanceOf(KnpPaginationFactory::class, $paginationFactory);

        $paginator = $paginationFactory->createPaginationForSource([], 1, 10, false);

        $this->assertEquals('some_route_index', $paginator->getCustomParameter('firstPageRoute'));
        $this->assertEquals('some_route_pagination', $paginator->getRoute());
    }

    private function getFilterControllerArgumentsEvent($controller): FilterControllerArgumentsEvent
    {
        $stub = $this->createMock(FilterControllerArgumentsEvent::class);
        $stub->method('getController')
            ->willReturn($controller);

        $stub->method('getArguments')
            ->willReturn($this->arguments->all());

        $stub->method('setArguments')
            ->willReturnCallback(function($arguments) {
                $this->arguments->replace($arguments);
            });

        return $stub;
    }
}
