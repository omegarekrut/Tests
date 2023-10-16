<?php

namespace Tests\Unit\Domain\Company\Report;

use App\Domain\Company\Report\ReportDatePeriodGenerator;
use DateInterval;
use DateTimeImmutable;
use Tests\Unit\TestCase;

class ReportDatePeriodGeneratorTest extends TestCase
{
    public function testCreateReportDatePeriod(): void
    {
        $today = new DateTimeImmutable();

        $dayFrom = $today->sub(new DateInterval('P30D'));
        $dayTo = $today->sub(new DateInterval('P14D'));

        $reportDatePeriodGenerator = new ReportDatePeriodGenerator();

        $reportDatePeriod = $reportDatePeriodGenerator->createFromDates($dayFrom, $dayTo);

        $this->assertEquals($dayFrom->getTimestamp(), $reportDatePeriod->getDatePeriod()->getStartDate()->getTimestamp());
        $this->assertGreaterThan($dayTo->getTimestamp(), $reportDatePeriod->getDatePeriod()->getEndDate()->getTimestamp());
    }
}
