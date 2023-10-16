<?php

namespace Tests\Functional\Util\BBCode;

use JBBCode\Parser;
use Tests\Functional\TestCase;

class JBBCodeParserTest extends TestCase
{
    /** @var Parser */
    private $bbcodeParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bbcodeParser = $this->getContainer()->get(Parser::class);
    }

    protected function tearDown(): void
    {
        unset($this->bbcodeParser);

        parent::tearDown();
    }

    /**
     * @dataProvider getTextWithBBCodesAndParsedHtml
     */
    public function testParseCanParseAllowedCodes(string $testWithBBCodes, string $expectedParsedHtml): void
    {
        $html = $this->bbcodeParser->parse($testWithBBCodes)->getAsHTML();

        $this->assertEquals($expectedParsedHtml, $html);
    }

    public function testNotAllowedCodesMustNotBeParsed(): void
    {
        $notParsableText = '
            [quote]text[/quote]
            [right]text[/right]
            [left]text[/left]
            [center]text[/center]
            [color=#ffff]text[/color]
            [table][tr][td]text[/td][/tr][/table]
            [code]code[/code]
        ';

        $html = $this->bbcodeParser->parse($notParsableText)->getAsHTML();

        $this->assertEquals($notParsableText, $html);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function getTextWithBBCodesAndParsedHtml(): \Generator
    {
        yield 'B tag can be parsed' => [
            '[b]text[/b]',
            '<b>text</b>',
        ];

        yield 'I tag can be parsed' => [
            '[i]text[/i]',
            '<i>text</i>',
        ];

        yield 'U tag can be parsed' => [
            '[u]text[/u]',
            '<u>text</u>',
        ];

        yield 'S tag can be parsed' => [
            '[s]text[/s]',
            '<s>text</s>',
        ];

        yield 'IMG tag can be parsed' => [
            '[img]image.jpg[/img]',
            '<img src="image.jpg">',
        ];

        yield 'Nested tags in IMG cannot be parsed' => [
            '[img][u]image.jpg[/u][/img]',
            '<img src="[u]image.jpg[/u]">',
        ];

        yield 'H2 tag can be parsed' => [
            '[h2]text[/h2]',
            '<h2>text</h2>',
        ];

        yield 'H3 tag can be parsed' => [
            '[h3]text[/h3]',
            '<h3>text</h3>',
        ];

        yield 'UL tag can be parsed' => [
            '[ul]text[/ul]',
            '<ul>text</ul>',
        ];

        yield 'OL tag can be parsed' => [
            '[ol]text[/ol]',
            '<ol>text</ol>',
        ];

        yield 'LI tag can be parsed' => [
            '[li]text[/li]',
            '<li>text</li>',
        ];

        yield 'YOUTUBE tag can be parsed' => [
            '[youtube]video-id[/youtube]',
            '<div class="mb15"><div class="js-has-video"><iframe width="640" height="360" src="//www.youtube.com/embed/video-id?wmode=opaque" data-youtube-id="video-id" frameborder="0" allowfullscreen></iframe></div></div>',  // @phpcsSuppress Generic.Files.LineLength
        ];

        yield 'Nested tags in YOUTUBE cannot be parsed' => [
            '[youtube][u]video-id[/u][/youtube]',
            '<div class="mb15"><div class="js-has-video"><iframe width="640" height="360" src="//www.youtube.com/embed/[u]video-id[/u]?wmode=opaque" data-youtube-id="[u]video-id[/u]" frameborder="0" allowfullscreen></iframe></div></div>',  // @phpcsSuppress Generic.Files.LineLength
        ];

        yield 'URL tag can be parsed' => [
            '[url=http://url]text[/url]',
            '<a href="http://url">text</a>',
        ];

        yield 'URL tag cannot be parsed if it contains invalid url' => [
            '[url=invalid-url]text[/url]',
            '[url=invalid-url]text[/url]',
        ];

        yield 'ATTACH tag can be parsed' => [
            '[attach]image.jpg[/attach]',
            '<img src="image.jpg" />',
        ];

        yield 'Nested tags in ATTACH cannot be parsed' => [
            '[attach][u]image.jpg[/u][/attach]',
            '<img src="[u]image.jpg[/u]" />',
        ];
    }
}
