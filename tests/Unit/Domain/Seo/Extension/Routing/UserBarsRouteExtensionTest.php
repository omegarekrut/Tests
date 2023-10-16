<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\UserBarsRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class UserBarsRouteExtensionTest extends TestCase
{
    /** @var SeoPage */
    private $seoPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
    }

    public function testSupportedRoute(): void
    {
        $route = new Route('index_userbars', new Uri(''), [
            'slug' => 'category-slug',
        ]);

        $userBarsRouteExtension = new UserBarsRouteExtension();

        $this->assertTrue($userBarsRouteExtension->isSupported($route));

        $userBarsRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertEquals('Рыбацкие юзербары и кнопки. Линеечки для рыбаков.', $this->seoPage->getTitle());
        $this->assertEquals('Здесь вы можете создать собственный рыбацкий юзербар 
            (линейку для форума) с уникальным текстом. С его помощью можно сообщить всем о своем личном рекорде, 
            транспортном средстве или добавить в подпись какую-нибудь умную или просто веселую фразу.', $this->seoPage->getDescription());
    }

    public function testUnsupported(): void
    {
        $route = new Route('invalid_route', new Uri(''));

        $userBarsRouteExtension = new UserBarsRouteExtension();
        $this->assertFalse($userBarsRouteExtension->isSupported($route));
    }
}
