<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\ScriptTagWidthInSourceRemover;
use Tests\Unit\TestCase;

class ScriptTagWidthInSourceRemoverTest extends TestCase
{
    /**
     * @dataProvider getScriptTagsWithWidthInSource
     */
    public function testScriptTagShouldLoseWidthInSource(string $html, string $expectedHtml): void
    {
        $removeWidthInSourceFromScriptTag = new ScriptTagWidthInSourceRemover();
        $actualHtml = $removeWidthInSourceFromScriptTag($html);

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    public function getScriptTagsWithWidthInSource(): \Generator
    {
        yield [
            '<script src="http://foo.bar/?width=1000&amp;height=500&amp;"></script>',
            '<script src="http://foo.bar/?height=500&amp;"></script>',
        ];

        yield [
            '<script src="http://foo.bar/?height=500&amp;width=900&amp;"></script>',
            '<script src="http://foo.bar/?height=500&amp;"></script>',
        ];


        yield [
            '<script src="http://foo.bar/" width=1000&amp;></script>',
            '<script src="http://foo.bar/" width=1000&amp;></script>',
        ];

        yield [
            '<img src="http://foo.bar?height=500&amp;width=1000&amp;"/>',
            '<img src="http://foo.bar?height=500&amp;width=1000&amp;"/>',
        ];

        yield [
            '<script src="http://foo.bar/?height=500&amp;WidTh=1000&amp;"></script>',
            '<script src="http://foo.bar/?height=500&amp;"></script>',
        ];
    }
}
