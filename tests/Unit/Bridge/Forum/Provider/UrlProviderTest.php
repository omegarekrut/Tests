<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Exception\RouteNotFoundException;
use App\Bridge\Xenforo\Provider\Api\ClientApi;
use App\Bridge\Xenforo\Provider\Api\UrlProvider;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class UrlProviderTest extends TestCase
{
    private const EXPECTED_FORUM_HOME_ROUTE_PATH = '/forum/index.php';

    use ClientApiTrait;

    public function testGetRoute(): void
    {
        $cache = $this->getCache();

        $provider = new UrlProvider($this->getForumClientApi(), $cache);

        $forumRoutePath = $provider->getRoute('home');

        $this->assertEquals(self::EXPECTED_FORUM_HOME_ROUTE_PATH, $forumRoutePath);
        $this->assertNotEmpty($cache->getValues());
    }

    public function testGetRouteFromCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->method('has')
            ->willReturn(true);

        $cache
            ->method('get')
            ->willReturn([
                'homeFromCache' => self::EXPECTED_FORUM_HOME_ROUTE_PATH,
            ]);

        $provider = new UrlProvider($this->createMock(ClientApi::class), $cache);

        $forumRoutePath = $provider->getRoute('homeFromCache');

        $this->assertEquals(self::EXPECTED_FORUM_HOME_ROUTE_PATH, $forumRoutePath);
    }

    public function testGetNotExistsRoute(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route not found by name "home2"');

        $provider = new UrlProvider($this->getForumClientApi(), $this->getCache());

        $provider->getRoute('home2');
    }

    private function getForumClientApi(): ClientApi
    {
        return $this->createClientApi(
            'routs/get-list',
            null,
            [
                'home' => self::EXPECTED_FORUM_HOME_ROUTE_PATH,
            ]
        );
    }

    private function getCache(): ArrayCache
    {
        return new ArrayCache();
    }
}
