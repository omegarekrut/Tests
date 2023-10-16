<?php

namespace Tests\Functional\Module\Voting;

use App\Module\YandexZen\HtmlToZenContentFormatter;
use Tests\Functional\TestCase;

/**
 * @group yandex-zen
 */
class HtmlToZenContentFormatterTest extends TestCase
{
    /**
     * @var HtmlToZenContentFormatter
     */
    private $htmlToZenContentFormatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->htmlToZenContentFormatter = $this->getContainer()->get(HtmlToZenContentFormatter::class);
    }

    protected function tearDown(): void
    {
        unset($this->htmlToZenContentFormatter);

        parent::tearDown();
    }

    public function testHtmlImagesMustBeFormattedByZenRules(): void
    {
        $expectedZenContent = <<<EOD
<figure>
    <img src="http://foo.bar/image" width="100" height="200"/>
    <figcaption>
        source title. Фото 1
        <span class="copyright">Copyright</span>
    </figcaption>
</figure>

EOD;

        $zenContent = $this->htmlToZenContentFormatter->format(
            '<img src="http://foo.bar/image" title="source title" width="100" height="200">',
            'Copyright',
            'default title'
        );

        $this->assertEquals($expectedZenContent, $zenContent);
    }

    public function testImageWithoutTitleMustBeNamedByDefault(): void
    {
        $zenContent = $this->htmlToZenContentFormatter->format(
            '<img src="http://foo.bar/image" width="100" height="200">',
            'Copyright',
            'default title'
        );

        $this->assertStringContainsString('default title. Фото 1', $zenContent);
    }

    public function testFormattedImagesMustBeNumbered(): void
    {
        $twoHtmlImages = <<<EOD
<img src="http://foo.bar/first.image" title="first image" width="100" height="200">
<img src="http://foo.bar/second.image" title="second image" width="100" height="200">
EOD;

        $zenContent = $this->htmlToZenContentFormatter->format(
            $twoHtmlImages,
            'Copyright',
            'default title'
        );

        $this->assertStringContainsString('first image. Фото 1', $zenContent);
        $this->assertStringContainsString('second image. Фото 2', $zenContent);
    }

    public function testImageSizeMustBeResolvedAutomaticallyIfNotDefined(): void
    {
        $zenContent = $this->htmlToZenContentFormatter->format('<img src="http://foo.bar/image">', '', '');

        $this->assertStringContainsString('<img src="http://foo.bar/image" width="1000" height="800"/>', $zenContent);
    }

    public function testHtmlVideoIframeMustBeFormattedByZenRules(): void
    {
        $expectedZenContent = <<<EOD
<figure>
    <video width="1000" height="800">
        <source src="http://foo.bar/video" type="video/mp4"/>
    </video>
    <figcaption>
        source title
        <span class="copyright">Copyright</span>
    </figcaption>
</figure>

EOD;

        $zenContent = $this->htmlToZenContentFormatter->format(
            '<iframe src="http://foo.bar/video" title="source title"></iframe>',
            'Copyright',
            'default title'
        );

        $this->assertEquals($expectedZenContent, $zenContent);
    }

    public function testVideoWithoutTitleMustBeNamedByDefault(): void
    {
        $zenContent = $this->htmlToZenContentFormatter->format(
            '<iframe src="http://foo.bar/video"></iframe>',
            'Copyright',
            'default title'
        );

        $this->assertStringContainsString('default title', $zenContent);
    }

    /**
     * @dataProvider getNotAllowedTags
     */
    public function testContentMustBeCleanedOfNotAllowedTags(string $notAllowedHtmlTag): void
    {
        $zenContent = $this->htmlToZenContentFormatter->format($notAllowedHtmlTag, '', '');

        $this->assertEmpty($zenContent);
    }

    public function getNotAllowedTags(): \Generator
    {
        yield ['<a></a>'];

        yield ['<abbr></abbr>'];

        yield ['<address></address>'];

        yield ['<html></html>'];

        yield ['<body></body>'];
    }

    public function testAllowedTagsMustBeCleanedOfAttributes(): void
    {
        $htmlTagWithAttributes = '<b class="class-attribute">text</b>';

        $zenContent = $this->htmlToZenContentFormatter->format($htmlTagWithAttributes, '', '');

        $this->assertEquals('<b>text</b>', $zenContent);
    }
}
