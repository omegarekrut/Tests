<?php

namespace Tests\Unit\Util\StringFilter\BBCode;

use App\Util\StringFilter\BBCode\RemoveBBCodesFilter;
use Tests\Unit\TestCase;

class RemoveBBCodesFilterTest extends TestCase
{
    public function testDecoration(): void
    {
        $removeBBCodesFilter = new RemoveBBCodesFilter();

        $actualText = $removeBBCodesFilter('parse [b]result[/b] [invlid-tag]tag value[/invalid-tag]');

        $this->assertEquals('parse result tag value', $actualText);
    }
}
