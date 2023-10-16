<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Page\Entity\Metadata;
use App\Domain\Page\Entity\Page;
use App\Domain\Seo\Extension\Routing\PageRouteExtension;
use App\Module\Seo\Exception\ContextNotFoundByTypeException;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\Domain\Rss\Record\Chooser\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class PageRouteExtensionTest extends TestCase
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
        $pageRouteExtension = new PageRouteExtension();

        $this->assertTrue($pageRouteExtension->isSupported(new Route('page_display_important', new Uri(''))));
        $this->assertTrue($pageRouteExtension->isSupported(new Route('page_display', new Uri(''))));

        $pageRouteExtension->apply($this->seoPage, new SeoContext([$this->createPage()]));

        $this->assertEquals('meta title', $this->seoPage->getTitle());
        $this->assertEquals('meta description', $this->seoPage->getDescription());
        $this->assertEquals('page title', $this->seoPage->getH1());
    }

    public function testInvalidContextType(): void
    {
        $this->expectException(ContextNotFoundByTypeException::class);
        $this->expectExceptionMessage('Context not found by type "App\Domain\Page\Entity\Page"');

        $pageRouteExtension = new PageRouteExtension();
        $pageRouteExtension->apply($this->seoPage, new SeoContext([$this]));
    }

    public function testUnsupported(): void
    {
        $pageRouteExtension = new PageRouteExtension();

        $this->assertFalse($pageRouteExtension->isSupported(new Route('invalid_route_name', new Uri(''))));
    }

    private function createPage(): Page
    {
        $stub = $this->createMock(Page::class);
        $stub
            ->method('getMetadata')
            ->willReturn(new Metadata('meta title', 'meta description'))
        ;
        $stub
            ->method('getTitle')
            ->willReturn('page title')
        ;

        return $stub;
    }
}
