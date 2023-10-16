<?php

namespace Tests\Unit\Module\Seo\View\Factory;

use App\Module\Seo\TransferObject\Breadcrumb;
use App\Module\Seo\TransferObject\PagePagination;
use App\Module\Seo\TransferObject\SeoPage;
use App\Module\Seo\View\Factory\CanonicalLinkFactory;
use App\Module\Seo\View\Factory\DescriptionMetaFactory;
use App\Module\Seo\View\Factory\OpenGraphMetasFactory;
use App\Module\Seo\View\Factory\PageNavigationLinksFactory;
use App\Module\Seo\View\Factory\RobotsMetaFactory;
use App\Module\Seo\View\Factory\SeoPageViewFactory;
use App\Module\Seo\View\TextHelper\HeadingPreparerHelper;
use App\Module\Seo\View\TextHelper\TitlePreparerHelper;
use App\Module\Seo\View\ViewObject\Link;
use App\Module\Seo\View\ViewObject\Meta;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 * @group seo-view
 */
class SeoPageViewFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        $breadcrumb = new Breadcrumb('title', new Uri('/test/'));

        $pagePagination = new PagePagination(5, 10, '/prev', '/next');
        $seoPage = new SeoPage();
        $seoPage
            ->setTitle('title')
            ->setDescription('description')
            ->disableIndexingByRobots()
            ->setH1('h1')
            ->setImageUrl(new Uri('http://image.uri/'))
            ->setSiteName('site name')
            ->setPagination($pagePagination)
            ->setCanonicalLink(new Uri('http://canonical.link'))
            ->addBreadcrumb($breadcrumb)
        ;

        $descriptionMeta = $this->createMock(Meta::class);
        $robotsMeta = $this->createMock(Meta::class);
        $openGraphMetas = [$this->createMock(Meta::class)];
        $pageNavigationLinks = [$this->createMock(Link::class)];
        $canonicalLink = $this->createMock(Link::class);

        $factory = new SeoPageViewFactory(
            $this->createTitlePreparerHelper($seoPage, 'prepered title'),
            $this->createHeadingPreparer($seoPage, 'prepered h1'),
            $this->createDescriptionMetaFactory($seoPage, $descriptionMeta),
            $this->createRobotsMetaFactory($seoPage, $robotsMeta),
            $this->createOpenGraphMetasFactory($seoPage, $openGraphMetas),
            $this->createCanonicalLinkFactory($seoPage, $canonicalLink),
            $this->createNavigationLinksFactory($seoPage, $pageNavigationLinks)
        );
        $seoPageView = $factory->createForPage($seoPage);

        $this->assertEquals('prepered title', $seoPageView->getTitle());
        $this->assertEquals('prepered h1', $seoPageView->getHeading());
        $this->assertEquals([$breadcrumb], $seoPageView->getBreadcrumbs());

        $this->assertContains($descriptionMeta, $seoPageView->getMetas());
        $this->assertContains($robotsMeta, $seoPageView->getMetas());
        $this->assertContains($openGraphMetas[0], $seoPageView->getMetas());
        $this->assertContains($pageNavigationLinks[0], $seoPageView->getLinks());
        $this->assertContains($canonicalLink, $seoPageView->getLinks());
    }

    private function createTitlePreparerHelper(SeoPage $seoPage, string $title): TitlePreparerHelper
    {
        $stub = $this->createMock(TitlePreparerHelper::class);
        $stub
            ->method('prepareTitle')
            ->with($seoPage->getTitle(), $seoPage->getPagination()->getPageNumber())
            ->willReturn($title)
        ;

        return $stub;
    }

    private function createHeadingPreparer(SeoPage $seoPage, string $heading): HeadingPreparerHelper
    {
        $stub = $this->createMock(HeadingPreparerHelper::class);
        $stub
            ->method('prepareHeading')
            ->with($seoPage->getH1(), $seoPage->getPagination()->getPageNumber())
            ->willReturn($heading)
        ;

        return $stub;
    }

    private function createDescriptionMetaFactory(SeoPage $seoPage, Meta $meta): DescriptionMetaFactory
    {
        $stub = $this->createMock(DescriptionMetaFactory::class);
        $stub
            ->method('createMeta')
            ->with($seoPage)
            ->willReturn($meta)
        ;

        return $stub;
    }

    private function createRobotsMetaFactory(SeoPage $seoPage, Meta $meta): RobotsMetaFactory
    {
        $stub = $this->createMock(RobotsMetaFactory::class);
        $stub
            ->method('createMeta')
            ->with($seoPage)
            ->willReturn($meta)
        ;

        return $stub;
    }

    private function createOpenGraphMetasFactory(SeoPage $seoPage, array $metas): OpenGraphMetasFactory
    {
        $stub = $this->createMock(OpenGraphMetasFactory::class);
        $stub
            ->method('createMetas')
            ->with($seoPage)
            ->willReturn($metas)
        ;

        return $stub;
    }

    private function createCanonicalLinkFactory(SeoPage $seoPage, Link $link): CanonicalLinkFactory
    {
        $stub = $this->createMock(CanonicalLinkFactory::class);
        $stub
            ->method('createLink')
            ->with($seoPage)
            ->willReturn($link)
        ;

        return $stub;
    }

    private function createNavigationLinksFactory(SeoPage $seoPage, array $links): PageNavigationLinksFactory
    {
        $stub = $this->createMock(PageNavigationLinksFactory::class);
        $stub
            ->method('createLinks')
            ->with($seoPage)
            ->willReturn($links)
        ;

        return $stub;
    }
}
