<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\EndSentenceFilter;
use Tests\Unit\TestCase;

class EndSentenceFilterTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testDecoration(string $sourceSentence, string $expectedSentence): void
    {
        $endSentenceFilter = new EndSentenceFilter();

        $actualSentence = $endSentenceFilter($sourceSentence);

        $this->assertEquals($expectedSentence, (string) $actualSentence);
    }

    public function getCases(): array
    {
        return [
            'add dot' => [
                'sentence',
                'sentence.',
            ],
            'leave dot' => [
                'sentence.',
                'sentence.',
            ],
            'leave allowed symbol !' => [
                'sentence!',
                'sentence!',
            ],
            'leave allowed symbol ?' => [
                'sentence?',
                'sentence?',
            ],
        ];
    }
}
