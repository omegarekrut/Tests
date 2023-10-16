<?php

namespace Tests\Unit\Module\FuzzyKeywordSearch\Word\Factory;

use App\Module\FuzzyKeywordSearch\Word\Factory\WordsParser;
use Tests\Unit\TestCase;

class WordsParserTest extends TestCase
{
    private $wordsParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wordsParser = new WordsParser();
    }

    protected function tearDown(): void
    {
        unset($this->wordsParser);

        parent::tearDown();
    }

    public function testParsedWordsMustNotContainsHtmlLineBreaks(): void
    {
        $originalString = 'Lorem <br /> Ipsum';

        $words = $this->wordsParser->parseFromString($originalString);

        $this->assertEquals(['Lorem', 'Ipsum'], $words);
    }

    public function testParsedWordsMustNotContainsLineBreaks(): void
    {
        $originalString = sprintf('Lorem %s%s Ipsum%s', PHP_EOL, PHP_EOL, PHP_EOL);

        $words = $this->wordsParser->parseFromString($originalString);

        $this->assertEquals(['Lorem', 'Ipsum'], $words);
    }

    public function testParsedWordsMustNotContainsHtmlTags(): void
    {
        $originalString = '<u>Lorem</u> <b>Ipsum</b>';

        $words = $this->wordsParser->parseFromString($originalString);

        $this->assertEquals(['Lorem', 'Ipsum'], $words);
    }

    public function testParsedWordsMustNotContainsDotsAndCommas(): void
    {
        $originalString = 'Lorem, Ipsum.';

        $words = $this->wordsParser->parseFromString($originalString);

        $this->assertEquals(['Lorem', 'Ipsum'], $words);
    }

    public function testParsedWordsMustNotContainsSpaces(): void
    {
        $originalString = '   Lorem    Ipsum   ';

        $words = $this->wordsParser->parseFromString($originalString);

        $this->assertEquals(['Lorem', 'Ipsum'], $words);
    }
}
