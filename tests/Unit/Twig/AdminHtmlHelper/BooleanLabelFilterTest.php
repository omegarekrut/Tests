<?php

namespace Tests\Unit\Twig\AdminHtmlHelper;

use App\Twig\AdminHtmlHelper\BooleanLabelFilter;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class BooleanLabelFilterTest extends TestCase
{
    public function testTrueValueMustBeReplacedToYes(): void
    {
        $booleanLabelFilter = new BooleanLabelFilter();

        $this->assertEquals('<span class="label label-success">Да</span>', $booleanLabelFilter(true));
    }

    public function testFalseValueMustBeReplacedToNope(): void
    {
        $booleanLabelFilter = new BooleanLabelFilter();

        $this->assertEquals('<span class="label label-danger">Нет</span>', $booleanLabelFilter(false));
    }
}
