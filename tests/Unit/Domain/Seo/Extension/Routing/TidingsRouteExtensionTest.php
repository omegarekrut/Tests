<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Tidings\View\TidingsView;
use App\Domain\Seo\Extension\Routing\TidingsRouteExtension;
use App\Module\Author\View\AuthorView;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use App\Util\ImageStorage\Collection\ImageTransformerCollection;
use App\Util\ImageStorage\ImageTransformer;
use Carbon\Carbon;
use Symfony\Component\Form\FormView;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class TidingsRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private SeoPage $seoPage;
    private TidingsRouteExtension $registrationRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->registrationRouteExtension = new TidingsRouteExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock(), $this->createConfiguredVisitorMock());
    }

    public function testSeoDataForViewPage(): void
    {
        [$routeName, $context, $expectedSeoData] = $this->getTidingsViewRouteWithSeoData();

        $route = new Route($routeName, new Uri(''));

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext(array_merge($context, [$route])));

        $this->assertEquals($expectedSeoData['title'], $this->seoPage->getTitle());
        $this->assertEquals($expectedSeoData['h1'], $this->seoPage->getH1());
        $this->assertEquals($expectedSeoData['description'], $this->seoPage->getDescription());
        $this->assertCount(1, $this->seoPage->getBreadcrumbs());

        $this->assertNotEmpty($this->seoPage->getMicroData());

        $microdata = $this->seoPage->getMicroData();
        $this->assertEquals([
            '@context' => 'http://schema.org',
            '@type' => 'Article',
            'author' => 'username',
            'description' => $expectedSeoData['description'],
            'headline' => $expectedSeoData['h1'],
            'name' => $expectedSeoData['h1'],
            'image' => [''],
            'datePublished' => $expectedSeoData['dateCreatedAt'],
            'dateModified' => $expectedSeoData['dateUpdatedAt'],
        ], $microdata->getDataForJson());
    }

    public function getTidingsViewRouteWithSeoData(): array
    {
        $tidingMetadata = new RecordViewMetadata();
        $tidingMetadata->title = 'some title';
        $tidingMetadata->description = 'some description';

        $tidingsView = new TidingsView();
        $tidingsView->metadata = $tidingMetadata;

        $tidingsView->author = new AuthorView();
        $tidingsView->author->name = 'username';

        $tidingsView->images = new ImageTransformerCollection([$this->createMock(ImageTransformer::class)]);
        $tidingsView->createdAt = Carbon::now();
        $tidingsView->updatedAt = Carbon::tomorrow();
        $tidingsView->heading = 'some heading';

        return [
            'tidings_view',
            [
                $tidingsView,
            ],
            [
                'title' => $tidingsView->metadata->title,
                'h1' => $tidingsView->heading,
                'description' => $tidingsView->metadata->description,
                'dateCreatedAt' => $tidingsView->createdAt->format('Y-m-d\TH:i:sO'),
                'dateUpdatedAt' => $tidingsView->updatedAt->format('Y-m-d\TH:i:sO'),
            ],
        ];
    }

    public function testSeoForTidingsListRoute(): void
    {
        $route = new Route('tidings_list', new Uri(''));
        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView()]));

        $this->assertEquals('Вести с водоемов', $this->seoPage->getTitle());
        $this->assertEquals('Вести с водоемов', $this->seoPage->getH1());
    }

    public function testSeoForTidingsListByRegionRoute(): void
    {
        $route = new Route('tidings_list_by_region', new Uri(''));
        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView()]));

        $this->assertEquals('Вести с водоемов - Новосибирск', $this->seoPage->getTitle());
        $this->assertEquals('Вести с водоемов - Новосибирск', $this->seoPage->getH1());
    }

    public function testSeoForTidingsListRouteForSearch(): void
    {
        $route = new Route('tidings_list', new Uri(''));
        $formView = new FormView();
        $formView->vars = ['data' => json_decode(json_encode(['search' => 'Карась']))];

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, $formView]));

        $this->assertEquals('Вести с водоемов - поиск по тексту: "Карась"', $this->seoPage->getTitle());
        $this->assertEquals('Вести с водоемов - поиск по тексту: "Карась"', $this->seoPage->getH1());
    }

    /**
     * @dataProvider supportedRouteDataProvider
     */
    public function testSupportedRoute(string $routeName, bool $isSupport): void
    {
        $route = new Route($routeName, new Uri(''));

        $this->assertEquals($isSupport, $this->registrationRouteExtension->isSupported($route));
    }

    public function supportedRouteDataProvider(): \Generator
    {
        yield [
            'invalid_route',
            false,
        ];

        yield [
            'tidings_view',
            true,
        ];

        yield [
            'tidings_list',
            true,
        ];

        yield [
            'tidings_list_pagination',
            true,
        ];

        yield [
            'tidings_list_by_region',
            true,
        ];

        yield [
            'tidings_list_by_region_pagination',
            true,
        ];
    }
}
