<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\HtmlLineBreaksPreparer;
use Tests\Unit\TestCase;

class HtmlLineBreaksPreparerTest extends TestCase
{
    private const INVALID_LINE_BREAK_FORMAT = 'invalid format';

    private $htmlLineBreaksPreparerTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->htmlLineBreaksPreparerTest = new HtmlLineBreaksPreparer();
    }

    public function testPreparerThrowExceptionForUnsupportedFormat(): void
    {
        $this->expectException(\RuntimeException::class);

        ($this->htmlLineBreaksPreparerTest)('some text', self::INVALID_LINE_BREAK_FORMAT);
    }

    /**
     * @dataProvider textWithLineBreaksForBr
     */
    public function testHtmlLineBreaksPreparerViaBr(string $input, string $expected): void
    {
        $preparedText = ($this->htmlLineBreaksPreparerTest)(
            $input,
            HtmlLineBreaksPreparer::LINE_BREAK_VIA_BR_TAG_FORMAT
        );

        $this->assertEquals($expected, $preparedText);
    }

    public static function textWithLineBreaksForBr(): \Generator
    {
        yield [
            'single line',
            'single line',
        ];

        yield [
            "first line\nsecond line",
            "first line<br />\nsecond line",
        ];

        yield [
            "first line\n\nsecond line",
            "first line<br />\n<br />\nsecond line",
        ];

        yield [
            "first line\nsecond line\n",
            "first line<br />\nsecond line<br />\n",
        ];

        yield [
            "first line\n\nsecond line\n\n",
            "first line<br />\n<br />\nsecond line<br />\n<br />\n",
        ];
    }

    /**
     * @dataProvider textWithLineBreaksForTagP
     */
    public function testHtmlLineBreaksPreparerViaTagP(string $input, string $expected): void
    {
        $preparedText = ($this->htmlLineBreaksPreparerTest)(
            $input,
            HtmlLineBreaksPreparer::LINE_BREAK_VIA_P_TAG_FORMAT
        );

        $this->assertEquals($expected, $preparedText);
    }

    public static function textWithLineBreaksForTagP(): \Generator
    {
        yield [
            'single line',
            '<p>single line</p>',
        ];

        yield [
            "first line\nsecond line",
            '<p>first line</p><p>second line</p>',
        ];

        yield [
            "first line\n\nsecond line",
            '<p>first line</p><p>second line</p>',
        ];

        yield [
            "first line\nsecond line\n",
            '<p>first line</p><p>second line</p>',
        ];

        yield [
            "first line\n\nsecond line\n\n",
            '<p>first line</p><p>second line</p>',
        ];

        yield [
            "<ul>\n<li>first line</li>\n\n<li>second line\n\n</li>\n</ul>",
            '<ul><li>first line</li><li>second line</li></ul>',
        ];

        yield [
            "<ol>\n<li>first line</li>\n\n<li>second line\n\n</li>\n</ol>",
            '<ol><li>first line</li><li>second line</li></ol>',
        ];

        yield [
            '<span>single line</span>',
            '<p><span>single line</span></p>',
        ];

        yield [
            "<b>first line</b>\nsecond line",
            '<p><b>first line</b></p><p>second line</p>',
        ];

        yield [
            "<strong>first line</strong>\nsecond line",
            '<p><strong>first line</strong></p><p>second line</p>',
        ];

        yield [
            "<u>first line</u>\nsecond line",
            '<p><u>first line</u></p><p>second line</p>',
        ];

        yield [
            "<s>first line</s>\nsecond line",
            '<p><s>first line</s></p><p>second line</p>',
        ];

        yield [
            "<em>first line</em>\nsecond line",
            '<p><em>first line</em></p><p>second line</p>',
        ];

        yield [
            "<i>first line</i>\nsecond line",
            '<p><i>first line</i></p><p>second line</p>',
        ];

        yield [
            '<blockquote>single line</blockquote>',
            '<blockquote>single line</blockquote>',
        ];

        yield [
            '<div>single line</div>',
            '<div>single line</div>',
        ];

        yield [
            '<section>single line</section>',
            '<section>single line</section>',
        ];

        yield [
            '<pre>single line</pre>',
            '<pre>single line</pre>',
        ];

        yield [
            '<hr />',
            '<hr />',
        ];

        yield [
            '<h1>single line</h1>',
            '<h1>single line</h1>',
        ];

        yield [
            '<h2>single line</h2>',
            '<h2>single line</h2>',
        ];

        yield [
            '<p>single line</p>',
            '<p>single line</p>',
        ];

        yield [
            'some text <ul><li>list element</li></ul> some text',
            '<p>some text</p><ul><li>list element</li></ul><p>some text</p>',
        ];

        yield [
            'some text <ol><li>list element</li></ol> some text',
            '<p>some text</p><ol><li>list element</li></ol><p>some text</p>',
        ];

        yield [
            'some text <div>div element</div> some text',
            '<p>some text</p><div>div element</div><p>some text</p>',
        ];

        yield [
            'some text <blockquote>quote text</blockquote> some text',
            '<p>some text</p><blockquote>quote text</blockquote><p>some text</p>',
        ];

        yield [
            'some text <section>section element</section> some text',
            '<p>some text</p><section>section element</section><p>some text</p>',
        ];

        yield [
            'some text <pre>pre element</pre> some text',
            '<p>some text</p><pre>pre element</pre><p>some text</p>',
        ];

        yield [
            'some text <hr /> some text',
            '<p>some text</p><hr /><p>some text</p>',
        ];

        yield [
            'some text <h1>Heading</h1> some text',
            '<p>some text</p><h1>Heading</h1><p>some text</p>',
        ];

        yield [
            'some text <h2>Heading</h2> some text',
            '<p>some text</p><h2>Heading</h2><p>some text</p>',
        ];

        yield [
            'some text <table><tr><td>table cell</td></tr></table> some text',
            '<p>some text</p><table><tr><td>table cell</td></tr></table><p>some text</p>',
        ];

        yield [
            "<a href='#'>first line</a>\nsecond line",
            "<p><a href='#'>first line</a></p><p>second line</p>",
        ];

        yield [
            "<img src='#' alt='name'>\nsecond line",
            "<p><img src='#' alt='name'></p><p>second line</p>",
        ];

        yield [
            'text <strong>bold text</strong> text',
            '<p>text <strong>bold text</strong> text</p>',
        ];

        yield [
            "<table>\n<thead><td>\n<td>first line</td>\n</tr></thead>\n<tbody>\n<tr>\n<td>second line</td>\n</tr></tbody></table>",
            '<table><thead><td><td>first line</td></tr></thead><tbody><tr><td>second line</td></tr></tbody></table>',
        ];
    }
}
