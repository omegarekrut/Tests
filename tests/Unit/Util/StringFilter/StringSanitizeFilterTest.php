<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\StringSanitizeToLineFilter;
use Tests\Unit\TestCase;

class StringSanitizeFilterTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testDecoration(string $sourceSentence, string $expectedSentence): void
    {
        $stringSanitizeFilter = new StringSanitizeToLineFilter();

        $actualSentence = $stringSanitizeFilter($sourceSentence);

        $this->assertEquals($expectedSentence, (string) $actualSentence);
    }

    public function getCases(): array
    {
        return [
            'strip tags' => [
                '<b>tag content</b>',
                'tag content',
            ],
            'html special chars' => [
                '"quoted text"',
                '&quot;quoted text&quot;',
            ],
            'remove enter' => [
                "paragraph\r\nparagraph",
                'paragraph paragraph',
            ],
            'remove space duplicates' => [
                'word    word',
                'word word',
            ],
            'html entity decode' => [
                '&lt;b&gt;bold&lt;/b&gt;',
                'bold',
            ],
            'trim' => [
                '   string   ',
                'string',
            ],
        ];
    }
}
