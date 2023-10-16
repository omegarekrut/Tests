<?php

namespace Tests\Unit\Module\Seo\View;

use App\Module\Seo\TransferObject\Breadcrumb;
use App\Module\Seo\TransferObject\MicroFormatData;
use App\Module\Seo\View\SeoHtmlRenderer;
use App\Module\Seo\View\ViewObject\Link;
use App\Module\Seo\View\ViewObject\Meta;
use App\Module\Seo\View\ViewObject\SeoPageView;
use Tests\Unit\TestCase;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use WhiteOctober\BreadcrumbsBundle\Templating\Helper\BreadcrumbsHelper;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 * @group seo-view
 */
class SeoHtmlRendererTest extends TestCase
{
    public function testInjection(): void
    {
        $seoPageView = $this->createSeoPageView([
            'getTitle' => 'new title',
            'getHeading' => 'new heading',
            'getMetas' => [
                new Meta('', 'meta property', 'meta content'),
                new Meta('meta name', '', 'meta content'),
            ],
            'getLinks' => [
                new Link('link rel', 'link href'),
            ],
            'getBreadcrumbs' => [
                new Breadcrumb('category', new Uri('/category-url/')),
            ],
            'getMicroFormatData' => new MicroFormatData('TestType', 'title', 'description', 'username'),
        ]);

        $renderer = new SeoHtmlRenderer($this->getBreadcrumbsHelperMock(), $this->getBreadcrumbsMock(true));
        $actualHtmlPage = $renderer->injectToTemplate($seoPageView, $this->getFixtureHtmlContent());

        $this->assertStringContainsString('<title>new title</title>', $actualHtmlPage, 'Assert title updated');
        $this->assertEquals(1, substr_count($actualHtmlPage, '<title'));

        $this->assertStringContainsString('<div class="breadcrumbs"></div>', $actualHtmlPage, 'Assert breadcrumbs update');
        $this->assertStringNotContainsString('<breadcrumbs></breadcrumbs>', $actualHtmlPage, 'Assert breadcrumbs tag is updated');

        $this->assertStringContainsString('<h1 class="heading-class" id="headingId">new heading</h1>', $actualHtmlPage, 'Assert heading updated');
        $this->assertEquals(1, substr_count($actualHtmlPage, '<h1'));

        $this->assertStringContainsString('<script type="application/ld+json">{"@context":"http:\/\/schema.org","@type":"TestType","author":"username","description":"description","headline":"title","name":"title"}</script>', $actualHtmlPage, 'Assert jsonLd');

        $actualPageHead = $this->getContentFromTag($actualHtmlPage, 'head');

        $this->assertStringContainsString('<meta property="meta property" content="meta content"/>', $actualPageHead, 'Assert meta added');
        $this->assertStringContainsString('<meta name="meta name" content="meta content"/>', $actualPageHead, 'Assert named meta added');
        $this->assertStringContainsString('<link rel="link rel" href="link href" />', $actualPageHead, 'Assert link added');
    }

    public function testDoNotOverwriteWithEmptyValues(): void
    {
        $seoPageView = $this->createSeoPageView([
            'getTitle' => '',
            'getHeading' => '',
            'getMetas' => [],
            'getLinks' => [],
            'getBreadcrumbs' => [],
        ]);

        $renderer = new SeoHtmlRenderer($this->getBreadcrumbsHelperMock(), $this->getBreadcrumbsMock(false));
        $actualHtmlPage = $renderer->injectToTemplate($seoPageView, $this->getFixtureHtmlContent());
        $this->assertStringContainsString('<title>title</title>', $actualHtmlPage, 'Assert title doesn\'t update');

        $this->assertStringContainsString('<div class="breadcrumbs"></div>', $actualHtmlPage, 'Assert breadcrumbs doesn\'t update');

        $heading = $this->getContentFromTag($actualHtmlPage, 'h1');
        $this->assertStringContainsString('heading <a href="/">heading link</a>', $heading, 'Assert heading doesn\'t update', true);
    }

    private function createSeoPageView(array $methodCallMap): SeoPageView
    {
        $stub = $this->createMock(SeoPageView::class);

        foreach ($methodCallMap as $methodName => $returnValue) {
            $stub
                ->method($methodName)
                ->willReturn($returnValue)
            ;
        }

        return $stub;
    }

    private function getFixtureHtmlContent(): string
    {
        return file_get_contents($this->getDataFixturesFolder().'html-page.html');
    }

    private function getContentFromTag(string $sourceString, string $tagName): string
    {
        $pattern = sprintf('/(\<%s.*?\>)(.|\n)*?(\<\/%s\>)/i', $tagName, $tagName);
        $matches = [];

        if (preg_match($pattern, $sourceString, $matches)) {
            return current($matches);
        }

        return '';
    }

    private function getBreadcrumbsHelperMock(): BreadcrumbsHelper
    {
        $mock = $this->createMock(BreadcrumbsHelper::class);
        $mock->expects($this->once())
            ->method('breadcrumbs')
            ->willReturnCallback(function () {
                return '<div class="breadcrumbs"></div>';
            });

        return $mock;
    }

    private function getBreadcrumbsMock($isCalled): Breadcrumbs
    {
        $mock = $this->createMock(Breadcrumbs::class);

        if ($isCalled) {
            $mock
                ->expects($this->once())
                ->method('prependItem')
                ->with('category', '/category-url/');
        } else {
            $mock
                ->expects($this->never())
                ->method('prependItem');
        }

        return $mock;
    }
}
