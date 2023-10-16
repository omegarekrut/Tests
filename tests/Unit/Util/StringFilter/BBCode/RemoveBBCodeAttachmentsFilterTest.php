<?php

namespace Tests\Unit\Util\StringFilter\BBCode;

use App\Util\StringFilter\BBCode\RemoveBBCodeAttachmentsFilter;
use Tests\Unit\TestCase;

class RemoveBBCodeAttachmentsFilterTest extends TestCase
{
    public function testDecoration(): void
    {
        $removeBBCodeAttachmentsFilter = new RemoveBBCodeAttachmentsFilter();

        $actualText = $removeBBCodeAttachmentsFilter('parse [b]result[/b] [img]image here[/img] [invlid-tag]tag value[/invalid-tag]');

        $this->assertEquals('parse [b]result[/b]   [invlid-tag]tag value[/invalid-tag]', $actualText);
    }
}
