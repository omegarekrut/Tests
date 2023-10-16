<?php

namespace Tests\Unit\Twig\DateTime;

use App\Twig\DateTime\DateDifferenceCalculatorForHumans;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class DateDifferenceCalculatorForHumansTest extends TestCase
{
    public function testCalculatorIsWork(): void
    {
        $calculator = new DateDifferenceCalculatorForHumans();
        $diff = $calculator(Carbon::now()->addDay()->addSecond());

        $this->assertEquals('1 день', $diff);
    }
}
