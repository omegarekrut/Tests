<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\SubSentenceFilter;
use Tests\Unit\TestCase;

class SubSentenceFilterTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testDecoration(string $sourceSentence, string $expectedSentence, int $textLength): void
    {
        $subSentenceFilter = new SubSentenceFilter();

        $actualSentence = $subSentenceFilter($sourceSentence, $textLength);

        $this->assertEquals($expectedSentence, (string) $actualSentence);
    }

    public function getCases(): array
    {
        return [
            'do nothing' => [
                'foo',
                'foo',
                10
            ],
            'sub sentence' => [
                'Get the most accurate and relevant corrections for your specific writing situation.',
                'Get the most accurate and relevant corrections...',
                50
            ],
            'sub sentence filters characters at the end of the text' => [
                'Get the most accurate and relevant corrections... for your specific writing situation.',
                'Get the most accurate and relevant corrections...',
                50
            ],
            'sub sentence filters characters at the end of the russian text' => [
                'Считается, что основа фольклорных сказок- тотемические мифы первобытного общества',
                'Считается, что основа фольклорных сказок...',
                50
            ],
            'sub sentence filters characters at the end of the word' => [
                'Morning! Get the most accurate and relevant',
                'Morning...',
                5
            ],
        ];
    }
}
