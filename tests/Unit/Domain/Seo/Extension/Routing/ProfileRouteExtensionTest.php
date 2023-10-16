<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\ProfileRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\Factory\BreadcrumbsFactory;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group route-extension
 */
class ProfileRouteExtensionTest extends TestCase
{
    /** @var SeoPage */
    private $seoPage;
    /** @var ProfileRouteExtension */
    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $breadCrumbFactory = $this->createMock(BreadcrumbsFactory::class);
        $this->pageRouteExtension = new ProfileRouteExtension($breadCrumbFactory);
    }

    /**
     * @dataProvider getProfileRoutes
     */
    public function testExtensionShouldSupportsAllProfileRoutes(string $routeName): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->assertTrue($this->pageRouteExtension->isSupported($route));
    }

    public function testExtensionCantSupportsAnyRoutes(): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => 'unsupported_route',
        ]);

        $this->assertFalse($this->pageRouteExtension->isSupported($route));
    }

    /**
     * @dataProvider getProfileRoutes
     */
    public function testSeoPageMustGetBreadcrumbAfterAppling(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertCount(1, $this->seoPage->getBreadcrumbs());
    }

    /**
     * @return string[][]
     */
    public function getProfileRoutes(): array
    {
        return [
            ['profile_edit_basic'],
            ['profile_edit_fishing_information'],
            ['profile_change_password'],
            ['profile_notifications'],
        ];
    }
}
