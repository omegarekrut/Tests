<?php

namespace Tests\Unit\Module\Seo\Extension;

use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\Extension\SourceSeoByContentExtension;
use App\Module\Seo\TransferObject\SeoPage;
use App\Module\Seo\TransferObject\SourcePageContent;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class SourceSeoByContentExtensionTest extends TestCase
{
    public function testBasicSeoInformationMustBeParsedFromSourcePage(): void
    {
        $seoPage = new SeoPage();
        $extension = new SourceSeoByContentExtension();
        $contextWithSourceContent = new SeoContext([new SourcePageContent($this->getSourcePageWithFilledSeoData())]);

        $extension->apply($seoPage, $contextWithSourceContent);

        $this->assertEquals('source title', $seoPage->getTitle());
        $this->assertEquals('source h1', $seoPage->getH1());
        $this->assertEquals('source description', $seoPage->getDescription());
    }

    public function testSeoInformationShouldNotBeReplacedToEmptyValues(): void
    {
        $seoPage = new SeoPage();
        $seoPage->setTitle('title');
        $seoPage->setH1('h1');
        $seoPage->setDescription('description');

        $sourceSeoPage = clone $seoPage;

        $extension = new SourceSeoByContentExtension();
        $contextWithEmptySourceContent = new SeoContext([new SourcePageContent('')]);

        $extension->apply($seoPage, $contextWithEmptySourceContent);

        $this->assertEquals($sourceSeoPage->getTitle(), $seoPage->getTitle());
        $this->assertEquals($sourceSeoPage->getH1(), $seoPage->getH1());
        $this->assertEquals($sourceSeoPage->getDescription(), $seoPage->getDescription());
    }

    private function getSourcePageWithFilledSeoData(): string
    {
        return <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <title>source title</title>
        <meta name="description" content="source description"/>
    </head>
    <body>
        <h1 class="heading-class" id="headingId">source h1</h1>
    </body>
</html>
EOT;
    }
}
