<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\RemoveLineBreakForInlineTagFilter;
use Tests\Unit\TestCase;
use Generator;

class RemoveLineBreakForInlineTagFilterTest extends TestCase
{
    /**
     * @dataProvider getRemoveLineBreakForInlineTagFilterTestData
     */
    public function testRemoveLineBreakForInlineTagFilterTest(string $expectedString, string $inputString): void
    {
        $removeWidthInSourceFromScriptTag = new RemoveLineBreakForInlineTagFilter();
        $actualString = $removeWidthInSourceFromScriptTag($inputString);

        $this->assertEquals($expectedString, $actualString);
    }

    public function getRemoveLineBreakForInlineTagFilterTestData(): Generator
    {
        yield [
            '[ul]SomeText[/ul]',
            "[ul]\r\nSomeText[/ul]",
        ];

        yield [
            "[ul]SomeText[/ul]\r\n",
            "[ul]\r\nSomeText[/ul]\r\n",
        ];

        yield [
            "[li]\r\nSomeText[/li]",
            "[li]\r\nSomeText[/li]",
        ];

        yield [
            "[li]\r\nSomeText[/li]",
            "[li]\r\nSomeText[/li]\r\n",
        ];

        yield [
            "[ol]\r\nSomeText[/ol]",
            "[ol]\r\nSomeText[/ol]",
        ];

        yield [
            "[ol]\r\nSomeText[/ol]",
            "[ol]\r\nSomeText[/ol]\r\n",
        ];

        yield [
            "[unsupported]\r\nSomeText[/unsupported]\r\n",
            "[unsupported]\r\nSomeText[/unsupported]\r\n",
        ];
    }
}
