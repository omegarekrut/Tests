<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Landing\Entity\Landing;
use App\Domain\Landing\Entity\ValueObject\MetaData;
use App\Domain\Seo\Extension\Routing\LandingExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class LandingExtensionTest extends TestCase
{
    /** @var SeoPage */
    private $seoPage;
    /** @var LandingExtension */
    private $landingRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $this->landingRouteExtension = new LandingExtension();
    }

    public function testSeoDataForLandingViewWithRecords(): void
    {
        $route = new Route('landing_view', new Uri(''));
        $metaData = new MetaData('landing title', 'landing description');
        $hashTag = new Hashtag('tag');
        $landing = new Landing($hashTag, 'landing h1', 'landing');
        $landing->rewriteMetaData($metaData);

        $this->landingRouteExtension->apply($this->seoPage, new SeoContext([$route, $landing]));

        $this->assertEquals('landing title', $this->seoPage->getTitle());
        $this->assertEquals('landing h1', $this->seoPage->getH1());
        $this->assertEquals('landing description', $this->seoPage->getDescription());
    }

    /**
     * @dataProvider getRoutesForCheckSupports
     */
    public function testIsSupportedRoutes(string $routeName, bool $expectedIsSupported): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->assertEquals($expectedIsSupported, $this->landingRouteExtension->isSupported($route));
    }

    public function getRoutesForCheckSupports(): array
    {
        return [
            'landing_view_route' => ['landing_view', true],
            'landing_view_pagination_route' => ['landing_view_pagination', true],
            'landing_view_fail_route' => ['landing_view_fail_route', false],
        ];
    }
}
