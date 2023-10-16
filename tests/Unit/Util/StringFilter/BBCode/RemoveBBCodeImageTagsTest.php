<?php

namespace Tests\Unit\Util\StringFilter\BBCode;

use App\Util\StringFilter\BBCode\RemoveBBCodeImageTagsFilter;
use Tests\Unit\TestCase;

class RemoveBBCodeImageTagsTest extends TestCase
{
    public function testFilteringShouldRemoveBBCodeImage(): void
    {
        $removeBBCodeImageTagsFilter = new RemoveBBCodeImageTagsFilter();

        $actualText = $removeBBCodeImageTagsFilter('text with [b]bbcode[/b] [image="http://some/image"]');

        $this->assertEquals('text with [b]bbcode[/b] ', $actualText);
    }
}
