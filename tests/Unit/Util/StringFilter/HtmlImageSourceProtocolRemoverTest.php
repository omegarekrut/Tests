<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\HtmlImageSourceProtocolRemover;
use Tests\Unit\TestCase;

class HtmlImageSourceProtocolRemoverTest extends TestCase
{
    /**
     * @dataProvider getHtmlWithAbsoluteImageSources
     */
    public function testAnyHtmlImageTagSourceShouldLoseProtocol(
        string $htmlWithAbsoluteImageSources,
        string $expectedTextWithRelativeImageSource
    ): void {

        $removeHtmlImageSourceProtocol = new HtmlImageSourceProtocolRemover();
        $actualHtml = $removeHtmlImageSourceProtocol($htmlWithAbsoluteImageSources);

        $this->assertEquals($expectedTextWithRelativeImageSource, $actualHtml);
    }

    public function getHtmlWithAbsoluteImageSources(): \Generator
    {
        yield [
            '<img src="http://foo.bar"/>',
            '<img src="//foo.bar"/>',
        ];

        yield [
            '<img class="image" src="http://foo.bar">',
            '<img class="image" src="//foo.bar">',
        ];

        yield [
            '<img src="http://foo.bar" />',
            '<img src="//foo.bar" />',
        ];

        yield [
            '<img src="https://foo.bar"/>',
            '<img src="//foo.bar"/>',
        ];

        yield [
            '<img class="image" src="https://foo.bar">',
            '<img class="image" src="//foo.bar">',
        ];

        yield [
            '<img src="https://foo.bar" />',
            '<img src="//foo.bar" />',
        ];

        yield [
            '<img src="//foo.bar"/>',
            '<img src="//foo.bar"/>',
        ];

        yield [
            '<img src="http://foo.bar"/><p><img src="https://foo.bar"/></p>',
            '<img src="//foo.bar"/><p><img src="//foo.bar"/></p>',
        ];

        yield [
            'http://foo.bar"/',
            'http://foo.bar"/',
        ];

        yield [
            '<img src=\'http://foo.bar\'/>',
            '<img src=\'//foo.bar\'/>',
        ];

        yield [
            '<img src="HtTp://foo.bar"/>',
            '<img src="//foo.bar"/>',
        ];
    }
}
