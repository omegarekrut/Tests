<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Video\View\VideoView;
use App\Domain\Seo\Extension\Routing\VideoRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Symfony\Component\Form\FormView;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @todo refactoring
 */
class VideoRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private SeoPage $seoPage;
    private VideoRouteExtension $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $this->pageRouteExtension = new VideoRouteExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock(), $this->createConfiguredVisitorMock());
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

    public function getRoutesForCheckSupports(): array
    {
        return [
            'video_list_route' => ['video_list', true],
            'video_list_by_region_route' => ['video_list_by_region', true],
            'video_list_pagination_route' => ['video_list_pagination', true],
            'video_category_list_route' => ['video_category_list', true],
            'video_category_list_pagination_route' => ['video_category_list_pagination', true],
            'video_view_route' => ['video_view', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testSeoForIndexPage(): void
    {
        $route = new Route('video_list', new Uri(''));
        $category = new Category('Video', 'all video content', 'video');

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $category]));

        $this->assertEquals('Video', $this->seoPage->getTitle());
        $this->assertEquals('Video', $this->seoPage->getH1());
        $this->assertEquals('all video content', $this->seoPage->getDescription());
    }

    /**
     * @dataProvider getRoutesWithSeoData
     */
    public function testSeoDataApplyToPage(string $routeName, array $context, array $expectedSeoData): void
    {
        $route = new Route($routeName, new Uri(''));

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext(array_merge($context, [$route])));

        $this->assertEquals($expectedSeoData['title'], $this->seoPage->getTitle());
        $this->assertEquals($expectedSeoData['h1'], $this->seoPage->getH1());
        $this->assertEquals($expectedSeoData['description'], $this->seoPage->getDescription());
    }

    public function testSeoForVideoListByRegionRoute(): void
    {
        $route = new Route('video_list_by_region', new Uri(''));
        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView()]));

        $this->assertEquals('Видео о рыбалке - Новосибирск', $this->seoPage->getTitle());
        $this->assertEquals('Видео о рыбалке - Новосибирск', $this->seoPage->getH1());
    }

    public function getRoutesWithSeoData(): array
    {
        $videoMetadata = new RecordViewMetadata();
        $videoMetadata->title = 'some title';
        $videoMetadata->description = 'some description';

        $videoView = new VideoView();
        $videoView->metadata = $videoMetadata;
        $videoView->heading = 'some heading';

        return [
            'video_view_without_preview' => [
                'video_view',
                [
                    $videoView,
                ],
                [
                    'title' => $videoView->metadata->title,
                    'h1' => $videoView->heading,
                    'description' => $videoView->metadata->description,
                ],
            ],
        ];
    }
}
