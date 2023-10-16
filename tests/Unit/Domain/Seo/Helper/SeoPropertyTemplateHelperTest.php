<?php

namespace Tests\Unit\Module\Seo\View\TextHelper;

use App\Domain\Seo\Helper\SeoPropertyTemplateRenderHelper;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;

/**
 * @group seo
 * @group seo-view
 */
class SeoPropertyTemplateHelperTest extends TestCase
{
    private $seoPropertyTemplateRenderHelper;
    private $seoPage;
    private $templateParameters;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seoPropertyTemplateRenderHelper = new SeoPropertyTemplateRenderHelper();

        $this->seoPage = new SeoPage();
        $this->seoPage
            ->setTitle('Original Title')
            ->setH1('Original H1')
            ->setDescription('Original Description');

        $this->templateParameters = [
            '{title}' => 'Original Title',
            '{h1}' => 'Original H1',
            '{description}' => 'Original Description',
            '{year}' => date('Y'),
            '{month}' => $this->seoPropertyTemplateRenderHelper->getCurrentMonthName(),
            '{search}' => 'судак',
        ];
    }

    public function testRenderSeoDataTemplateWithEmptyTemplate(): void
    {
        $renderedTemplate = $this->seoPropertyTemplateRenderHelper->renderTemplate('', []);

        $this->assertEquals('', $renderedTemplate);
    }

    public function testRenderSeoDataTemplateWithTemplate(): void
    {
        $expectedRenderedTemplate = sprintf('Original Title Original H1 Original Description %s %s судак',
            date('Y'),
            $this->seoPropertyTemplateRenderHelper->getCurrentMonthName()
        );

        $template = '{title} {h1} {description} {year} {month} {search}';

        $renderedTemplate = $this->seoPropertyTemplateRenderHelper->renderTemplate($template, $this->templateParameters);

        $this->assertEquals($expectedRenderedTemplate, $renderedTemplate);
    }

    public function testPrepareTemplateParametersBySourcePageAndQueryStringWithQueryString(): void
    {
        $queryString = http_build_query(['aaa', 'search' => 'судак', 'bbb']);

        $actualParameters = $this->seoPropertyTemplateRenderHelper->prepareTemplateParametersBySourcePageAndQueryString($this->seoPage, $queryString);

        $this->assertEquals($this->templateParameters, $actualParameters);
    }

    public function testPrepareTemplateParametersBySourcePageAndQueryStringWithoutQueryString(): void
    {
        $actualParameters = $this->seoPropertyTemplateRenderHelper->prepareTemplateParametersBySourcePageAndQueryString($this->seoPage, '');

        $this->assertEquals(array_merge($this->templateParameters, ['{search}' => '']), $actualParameters);
    }

    public function testPrepareTemplateParametersBySourcePageAndQueryStringWithAbnormalQueryString(): void
    {
        $queryString = http_build_query(['aaa', 'search' => ['судак'], 'bbb']);

        $actualParameters = $this->seoPropertyTemplateRenderHelper->prepareTemplateParametersBySourcePageAndQueryString($this->seoPage, $queryString);

        $this->assertEquals(array_merge($this->templateParameters, ['{search}' => '']), $actualParameters);
    }
}
