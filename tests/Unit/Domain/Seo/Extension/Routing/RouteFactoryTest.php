<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\RouteFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
final class RouteFactoryTest extends TestCase
{
    public function testRouteCreatedFromSymfonyRoutes(): void
    {
        $request = $this->createSymfonyRequest('https://example.com/request/uri');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $requestMatcher = $this->createMock(RequestMatcherInterface::class);
        $requestMatcher
            ->method('matchRequest')
            ->willReturn(
                [
                    '_route' => 'route_name',
                    'parameter' => 'value',
                ]
            );

        $routeFactory = new RouteFactory($requestMatcher, $requestStack);
        $route = $routeFactory->createRoute();

        $this->assertEquals('route_name', $route->getName());
        $this->assertEquals('/request/uri', (string) $route->getUri());
        $this->assertEquals('value', (string) $route->getParameter('parameter', 'default'));
        $this->assertEquals('default', (string) $route->getParameter('undefined', 'default'));
    }

    private function createSymfonyRequest(string $requestUrl): Request
    {
        $components = parse_url($requestUrl);

        return new Request([], [], [], [], [], [
            'SERVER_NAME' => $components['host'],
            'REQUEST_URI' => $components['path'],
            'SERVER_PORT' => 80,
        ]);
    }
}
