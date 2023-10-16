<?php

namespace Tests\Unit\Domain\Statistic\Normalizer;

use App\Domain\Statistic\Normalizer\StatisticReportGoogleChartNormalizer;
use App\Domain\Statistic\Entity\StatisticReport\UsersActivityStatisticReport;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Tests\Unit\TestCase;

/**
 * @group statistic
 */
class StatisticReportGoogleChartNormalizerTest extends TestCase
{
    private const MILLI_MULTIPLIER = 1000;
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new StatisticReportGoogleChartNormalizer();

        parent::setUp();
    }

    public function testDailyReportNormalize(): void
    {
        $expectedReportUnit0 = [
            'date' => '01.01.2020',
            'count' => 10,
            'milliUnixtime' => Carbon::parse('2020-01-01')->getTimestamp() * self::MILLI_MULTIPLIER,
            'label' => "Ср, 01.01.2020\nновые пользователи: 10",
        ];
        $expectedReportUnit1 = [
            'date' => '02.01.2020',
            'count' => 11,
            'milliUnixtime' => Carbon::parse('2020-01-02')->getTimestamp() * self::MILLI_MULTIPLIER,
            'label' => "Чт, 02.01.2020\nновые пользователи: 11",
        ];
        $expectedReportUnit2 = [
            'date' => '03.01.2020',
            'count' => 12,
            'milliUnixtime' => Carbon::parse('2020-01-03')->getTimestamp() * self::MILLI_MULTIPLIER,
            'label' => "Пт, 03.01.2020\nновые пользователи: 12",
        ];

        $report = $this->createReport([$expectedReportUnit0, $expectedReportUnit1, $expectedReportUnit2], new DateInterval('P1D'));

        $normalizedReportUnits = $this->normalizer->normalize($report, 'новые пользователи');

        $this->assertCount(3, $normalizedReportUnits);
        $this->assertExpectedReportUnitEqualsToNormalized($expectedReportUnit0, $normalizedReportUnits[0]);
        $this->assertExpectedReportUnitEqualsToNormalized($expectedReportUnit1, $normalizedReportUnits[1]);
        $this->assertExpectedReportUnitEqualsToNormalized($expectedReportUnit2, $normalizedReportUnits[2]);
    }

    public function testWeeklyReportNormalize(): void
    {
        $expectedReportUnit0 = [
            'date' => '30.12.2019',
            'count' => 70,
            'milliUnixtime' => Carbon::parse('2020-01-01')->startOfWeek()->getTimestamp() * self::MILLI_MULTIPLIER,
            'label' => "30.12.2019 - 05.01.2020\nновые пользователи: 70",
        ];
        $expectedReportUnit1 = [
            'date' => '06.01.2020',
            'count' => 80,
            'milliUnixtime' => Carbon::parse('2020-01-06')->getTimestamp() * self::MILLI_MULTIPLIER,
            'label' => "06.01.2020 - 12.01.2020\nновые пользователи: 80",
        ];
        $expectedReportUnit2 = [
            'date' => '13.01.2020',
            'count' => 10,
            'milliUnixtime' => Carbon::parse('2020-01-13')->startOfWeek()->getTimestamp() * self::MILLI_MULTIPLIER,
            'label' => "13.01.2020 - 13.01.2020\nновые пользователи: 10",
        ];

        $report = $this->createReport([$expectedReportUnit0, $expectedReportUnit1, $expectedReportUnit2], new DateInterval('P1W'));

        $normalizedReportUnits = $this->normalizer->normalize($report, 'новые пользователи');

        $this->assertCount(3, $normalizedReportUnits);
        $this->assertExpectedReportUnitEqualsToNormalized($expectedReportUnit0, $normalizedReportUnits[0]);
        $this->assertExpectedReportUnitEqualsToNormalized($expectedReportUnit1, $normalizedReportUnits[1]);
        $this->assertExpectedReportUnitEqualsToNormalized($expectedReportUnit2, $normalizedReportUnits[2]);
    }

    /**
     * @param mixed[] $reportData
     */
    private function createReport(array $reportData, DateInterval $interval): UsersActivityStatisticReport
    {
        $periodFrom = Carbon::parse($reportData[0]['date']);
        $periodTo = Carbon::parse($reportData[count($reportData)-1]['date']);
        $period = new DatePeriod($periodFrom, $interval, $periodTo);

        return new UsersActivityStatisticReport($period, $reportData);
    }

    /**
     * @param mixed[] $expectedReportUnitData
     * @param mixed[] $normalizedReportUnit
     */
    private function assertExpectedReportUnitEqualsToNormalized(array $expectedReportUnitData, array $normalizedReportUnit): void
    {
        $this->assertEquals($expectedReportUnitData['date'], $normalizedReportUnit['date']);
        $this->assertEquals($expectedReportUnitData['count'], $normalizedReportUnit['value']);
        $this->assertEquals($expectedReportUnitData['milliUnixtime'], $normalizedReportUnit['milliUnixtime']);
        $this->assertEquals($expectedReportUnitData['label'], $normalizedReportUnit['label']);
    }
}
