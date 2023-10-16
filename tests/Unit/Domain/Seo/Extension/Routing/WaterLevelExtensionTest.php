<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\CustomInfoByUriExtension;
use App\Domain\Seo\Extension\Routing\WaterLevelExtension;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Domain\WaterLevel\View\GaugingStationView;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\Factory\BreadcrumbsFactory;
use App\Module\Seo\TransferObject\SeoPage;
use App\Twig\WaterLevel\WaterTypeFullNameTranslator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

class WaterLevelExtensionTest extends TestCase
{
    private SeoPage $seoPage;
    private WaterLevelExtension $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('/some-link/');

        $this->pageRouteExtension = new WaterLevelExtension($this->createBreadcrumbsFactoryMock(), $urlGenerator, new WaterTypeFullNameTranslator());
    }

    protected function tearDown(): void
    {
        unset(
            $this->seoPage,
            $this->pageRouteExtension
        );

        parent::tearDown();
    }

    /**
     * @dataProvider getRoutesForCheckSupports
     */
    public function testIsSupportedRoutes(string $routeName, bool $expectedIsSupported): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->assertEquals($expectedIsSupported, $this->pageRouteExtension->isSupported($route));
    }

    /**
     * @return mixed[]
     */
    public function getRoutesForCheckSupports(): array
    {
        return [
            'water_level_index' => ['water_level_index', true],
            'waters' => ['waters', true],
            'water' => ['water', true],
            'gauging_station' => ['gauging_station', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testGenerateSeoForViewGaugingStationPage(): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => 'gauging_station',
        ]);

        $gaugingStationView = new GaugingStationView();
        $gaugingStationView->name = 'Искитим';
        $gaugingStationView->viewPath = '/view-path/link';
        $gaugingStationView->water = $this->createMock(Water::class);
        $gaugingStationView->water->method('getType')
            ->willReturn(WaterType::river());
        $gaugingStationView->water->method('getName')
            ->willReturn('Бердь');

        $context = new SeoContext([
            $route,
            $gaugingStationView,
        ]);

        $this->pageRouteExtension->apply($this->seoPage, $context);

        $this->assertEquals($gaugingStationView->viewPath, (string) $this->seoPage->getCanonicalLink());
        $this->assertEquals('Уровень воды сегодня: Искитим, Река Бердь. График уровня воды, архивные данные - на рыболовном портале FishingSib.', $this->seoPage->getDescription());
        $this->assertCount(3, $this->seoPage->getBreadcrumbs());
    }

    private function createBreadcrumbsFactoryMock(): BreadcrumbsFactory
    {
        $customInfoByUriExtensionMock = $this->createMock(CustomInfoByUriExtension::class);
        $customInfoByUriExtensionMock->method('withUri')
            ->willReturnSelf();

        return new BreadcrumbsFactory($customInfoByUriExtensionMock);
    }
}
