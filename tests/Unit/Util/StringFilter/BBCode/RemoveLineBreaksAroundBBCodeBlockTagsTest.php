<?php

namespace Tests\Unit\Util\StringFilter\BBCode;

use App\Util\StringFilter\BBCode\RemoveLineBreaksAroundBBCodeBlockTags;
use Tests\Unit\TestCase;

class RemoveLineBreaksAroundBBCodeBlockTagsTest extends TestCase
{
    public function testDecoration(): void
    {
        $removeLineBreaksBetweenBBCodeBlockTags = new RemoveLineBreaksAroundBBCodeBlockTags();

        $actualText = $removeLineBreaksBetweenBBCodeBlockTags('[h1]test[/h1]
[h2]test2[/h2][img]
[img]
[ul]');

        $this->assertEquals("[h1]test[/h1][h2]test2[/h2][img]\n[img][ul]", $actualText);
    }
}
