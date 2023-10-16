<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\AddSuffixFilter;
use Tests\Unit\TestCase;

class AddSuffixFilterTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testDecorate(string $expectedString, string $source, string $suffix, string $separator): void
    {
        $addSuffixFilter = new AddSuffixFilter;

        $actualString = $addSuffixFilter($source, $suffix, $separator);

        $this->assertEquals($expectedString, (string) $actualString);
    }

    public function getCases(): array
    {
        return [
            'empty suffix with blank separator' => [
                'source',
                'source',
                '',
                ' ',
            ],
            'decoration with blank separator' => [
                'source suffix',
                'source',
                'suffix',
                ' ',
            ],
            'empty source with blank separator' => [
                'suffix',
                '',
                'suffix',
                ' ',
            ],
            'empty suffix with not blank separator' => [
                'source',
                'source',
                '',
                ' | ',
            ],
            'decoration with not blank separator' => [
                'source | suffix',
                'source',
                'suffix',
                ' | ',
            ],
            'empty source with not blank separator' => [
                'suffix',
                '',
                'suffix',
                ' | ',
            ],
        ];
    }
}
