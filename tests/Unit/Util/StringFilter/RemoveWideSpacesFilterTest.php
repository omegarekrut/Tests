<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\RemoveWideSpacesFilter;
use Tests\Unit\TestCase;

class RemoveWideSpacesFilterTest extends TestCase
{
    public function testDecoration(): void
    {
        $removeWideSpacesFilter = new RemoveWideSpacesFilter();

        $actualText = $removeWideSpacesFilter('parse       result           value                            spaces');

        $this->assertEquals('parse result value spaces', $actualText);
    }
}
