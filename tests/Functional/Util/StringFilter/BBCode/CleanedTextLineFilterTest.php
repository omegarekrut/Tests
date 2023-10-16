<?php

namespace Tests\Functional\Util\StringFilter\BBCode;

use App\Util\StringFilter\CleanedTextLineFilter;
use Tests\Functional\TestCase as FunctionalTestCase;

class CleanedTextLineFilterTest extends FunctionalTestCase
{
    /**
     * @dataProvider getTexts
     */
    public function testClearText(string $sourceSentence, string $expectedSentence): void
    {
        $cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->assertEquals($expectedSentence, $cleanedTextLineFilter($sourceSentence));
    }

    public function getTexts(): array
    {
        return [
            'text and link' => [
                'text https://www.site.ru/ text',
                'text https://www.site.ru/ text',
            ],
            'html tag cleanup' => [
                '<a href="" target="_blank" rel="nofollow">https://www.site.ru/</a>',
                'https://www.site.ru/',
            ],
            'bbcode cleanup' => [
                '[url=https://www.site.ru/]https://www.site.ru/[/url]',
                'https://www.site.ru/',
            ],
            'clear html tag if text is on the sides' => [
                'text <a href="" target="_blank" rel="nofollow">https://www.site.ru/</a> text',
                'text https://www.site.ru/ text',
            ],
            'clear bbcode tag if text is on the sides' => [
                'text [url=https://www.site.ru/]https://www.site.ru/[/url] text ',
                'text https://www.site.ru/ text',
            ],
        ];
    }
}
