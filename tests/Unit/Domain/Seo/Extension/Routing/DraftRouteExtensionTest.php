<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\DraftRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class DraftRouteExtensionTest extends TestCase
{
    private SeoPage $seoPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
    }

    /**
     * @dataProvider getRouteName
     */
    public function testUnsupportedForExclude(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));
        $draftRouteExtension = new DraftRouteExtension();

        $this->assertFalse($draftRouteExtension->isSupported($route));
    }

    public function getRouteName(): array
    {
        return [
            'contains draft word' => [
                'show_draft',
            ],
            'some page route' => [
                'page_display',
            ],
        ];
    }

    public function testDraftShowRoute(): void
    {
        $route = new Route('draft_show', new Uri(''));

        $draftRouteExtension = new DraftRouteExtension();

        $this->assertTrue($draftRouteExtension->isSupported($route));
    }

    public function testDisableIndexingByRobotsMustBeSuccess(): void
    {
        $route = new Route('draft_show', new Uri(''));

        $draftRouteExtension = new DraftRouteExtension();

        $draftRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertFalse($this->seoPage->isIndexingByRobotsEnabled());
    }
}
