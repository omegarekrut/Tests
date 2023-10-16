<?php

namespace Tests\Functional\Domain\WaterLevel\Repository;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Repository\GaugingStationRecordRepository;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\WaterLevel\LoadGaugingStationProvidersWithRecordForGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class GaugingStationRecordRepositoryTest extends TestCase
{
    private GaugingStationRecordRepository $gaugingStationRecordRepository;
    private GaugingStation $gaugingStation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gaugingStationRecordRepository = $this->getContainer()->get(GaugingStationRecordRepository::class);

        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskGaugingStation::class,
            LoadGaugingStationProvidersWithRecordForGaugingStation::class,
        ])->getReferenceRepository();

        $this->gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->gaugingStationRecordRepository,
            $this->gaugingStation
        );

        parent::tearDown();
    }

    public function testFindByGaugingStationAndYear(): void
    {
        $currentYear = Carbon::now()->year;

        $records = $this->gaugingStationRecordRepository->findByGaugingStationAndYear(
            $this->gaugingStation,
            $currentYear
        );

        foreach ($records as $record) {
            $this->assertEquals($this->gaugingStation, $record->getGaugingStationProvider()->getGaugingStation());
            $this->assertEquals($currentYear, (int) $record->getRecordedAt()->format('Y'));
        }
    }

    public function testFindRecordedYearsWithWaterLevel(): void
    {
        $recordedYears = $this->gaugingStationRecordRepository->findRecordedYearsWithWaterLevel(
            $this->gaugingStation
        );

        $expectedRecordedYearsCount = LoadGaugingStationProvidersWithRecordForGaugingStation::MAX_YEARS_COUNT;
        $expectedRecordedYears = self::getLatestYears($expectedRecordedYearsCount);

        $this->assertCount($expectedRecordedYearsCount, $recordedYears);

        foreach ($recordedYears as $recordedYear) {
            $this->assertContains($recordedYear, $expectedRecordedYears);
        }
    }

    public function testFindRecordedYearsWithTemperature(): void
    {
        $recordedYears = $this->gaugingStationRecordRepository->findRecordedYearsWithTemperature(
            $this->gaugingStation
        );

        $expectedRecordedYearsCount = LoadGaugingStationProvidersWithRecordForGaugingStation::MAX_YEARS_COUNT;
        $expectedRecordedYears = self::getLatestYears($expectedRecordedYearsCount);

        $this->assertCount($expectedRecordedYearsCount, $recordedYears);

        foreach ($recordedYears as $recordedYear) {
            $this->assertContains($recordedYear, $expectedRecordedYears);
        }
    }

    /**
     * @return int[]
     */
    private static function getLatestYears(int $yearCount): array
    {
        $yearsCountWithoutCurrentYear = $yearCount - 1;

        return range(Carbon::now()->subYears($yearsCountWithoutCurrentYear)->year, Carbon::now()->year);
    }
}
