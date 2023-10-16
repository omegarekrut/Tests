<?php

namespace Tests\Unit\Module\Seo\Extension\Route;

use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\Routing\RouteFactoryInterface;
use App\Module\Seo\Extension\Routing\RoutingBasedSeoExtension;
use App\Module\Seo\Extension\Routing\SeoRouteExtensionInterface;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class RoutingBasedSeoExtensionTest extends TestCase
{
    public function testChooseOneRoute(): void
    {
        $route = new Route('route_name', new Uri('/route/path'));
        $routeFactory = $this->createRouteFactory($route);

        $routingBasedSeoExtension = new RoutingBasedSeoExtension($routeFactory, [
            $this->createRouteExtension($route, 'unsupported extension', false, true),
            $this->createRouteExtension($route, 'first supported extension', true, true),
            $this->createRouteExtension($route, 'unsupported extension', false, false),
            $this->createRouteExtension($route, 'second supported extension', true, false),
        ]);

        $seoPage = new SeoPage();
        $routingBasedSeoExtension->apply($seoPage, new SeoContext([]));

        $this->assertEquals('first supported extension', $seoPage->getTitle());
    }

    public function testInvalidExtensionType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoutingBasedSeoExtension($this->createMock(RouteFactoryInterface::class), [
            $this,
        ]);
    }

    private function createRouteFactory(Route $route): RouteFactoryInterface
    {
        $stub = $this->createMock(RouteFactoryInterface::class);
        $stub
            ->expects($this->once())
            ->method('createRoute')
            ->willReturn($route)
        ;

        return $stub;
    }

    private function createRouteExtension(Route $route, string $title, bool $supported, bool $called): SeoRouteExtensionInterface
    {
        $stub = $this->createMock(SeoRouteExtensionInterface::class);

        if ($called) {
            $stub
                ->expects($this->once())
                ->method('isSupported')
                ->with($route)
                ->willReturn($supported);
        } else {
            $stub
                ->expects($this->never())
                ->method('isSupported')
                ->with($route)
                ->willReturn($supported);
        }

        $stub
            ->method('apply')
            ->willReturnCallback(function (SeoPage $seoPage, SeoContext $context) use ($route, $title) {
                $seoPage->setTitle($title);
                $this->assertEquals($route, $context->getOneByType(Route::class));
            })
        ;

        return $stub;
    }
}
