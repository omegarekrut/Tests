<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\AddPageNumberSuffixFilter;
use Tests\Unit\TestCase;

class AddPageNumberSuffixFilterTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testDecorate(string $expectedString, string $source, int $pageNumber): void
    {
        $addPageNumberSuffixFilter = new AddPageNumberSuffixFilter();

        $actualString = $addPageNumberSuffixFilter($source, $pageNumber);

        $this->assertEquals($expectedString, (string) $actualString);
    }

    public function getCases(): array
    {
        return [
            'empty page number' => [
                'source',
                'source',
                0,
            ],
            'first page' => [
                'source',
                'source',
                1,
            ],
            'second page' => [
                'source. Страница 2',
                'source',
                2,
            ],
        ];
    }
}
