<?php

namespace Tests\Unit\Domain\Company\Report\StatisticReport;

use App\Domain\Company\Report\StatisticReport\ReportDatePeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Tests\Unit\TestCase;

class ReportDatePeriodTest extends TestCase
{
    public function testMarkFormatForIntervalInDays(): void
    {
        $dateInterval = new DateInterval('P1D');
        $datePeriod = new DatePeriod(new DateTime(), $dateInterval, new DateTime());

        $reportDatePeriod = new ReportDatePeriod($datePeriod);

        $this->assertEquals('d.m', $reportDatePeriod->getDateFormat());
    }

    public function testMarkFormatForIntervalInMonths(): void
    {
        $dateInterval = new DateInterval('P1M');
        $datePeriod = new DatePeriod(new DateTime(), $dateInterval, new DateTime());

        $reportDatePeriod = new ReportDatePeriod($datePeriod);

        $this->assertEquals('m.Y', $reportDatePeriod->getDateFormat());
    }

    public function testMarkFormatForIntervalInYears(): void
    {
        $dateInterval = new DateInterval('P1Y');
        $datePeriod = new DatePeriod(new DateTime(), $dateInterval, new DateTime());

        $reportDatePeriod = new ReportDatePeriod($datePeriod);

        $this->assertEquals('m.Y', $reportDatePeriod->getDateFormat());
    }
}
