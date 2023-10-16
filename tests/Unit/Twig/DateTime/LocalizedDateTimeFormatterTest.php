<?php

namespace Tests\Unit\Twig\DateTime;

use App\Auth\Visitor\Visitor;
use App\Twig\DateTime\LocalizedDateTimeFormatter;
use Carbon\Carbon;
use DateTimeZone;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class LocalizedDateTimeFormatterTest extends TestCase
{
    public function testLocalizationIsWork(): void
    {
        $localizedDateTimeFormatter = new LocalizedDateTimeFormatter($this->getVisitor());
        $localizedDate = $localizedDateTimeFormatter(Carbon::parse('2019-05-27 12:00:00'), 'd MMMM yyyy');

        $this->assertEquals('27 мая 2019', $localizedDate);
    }

    private function getVisitor(): Visitor
    {
        $stub = $this->createMock(Visitor::class);
        $stub->method('getTimeZone')
            ->willReturn(new DateTimeZone('Asia/Novosibirsk'));

        return $stub;
    }
}
