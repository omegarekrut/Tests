<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\GaugingStationProviderFactory;

class LoadGaugingStationProvidersWithRecordForGaugingStation extends Fixture implements DependentFixtureInterface
{
    public const MAX_YEARS_COUNT = 3;

    private const MAX_RECORDS_IN_YEAR_BY_STATION = 30;

    private const MIN_WATER_LEVEL = 90;
    private const MAX_WATER_LEVEL = 140;

    private const MIN_TEMPERATURE = 10;
    private const MAX_TEMPERATURE = 30;

    private static array $gaugingStationReferences = [
        LoadNovosibirskGaugingStation::REFERENCE_NAME,
        LoadNovosibirskHydroelectricPowerStationGaugingStation::REFERENCE_NAME,
        LoadBerdskGaugingStation::REFERENCE_NAME,
        LoadHideBerdskGaugingStation::REFERENCE_NAME,
        LoadHideNovosibirskGaugingStation::REFERENCE_NAME,
        LoadHideGornoAltayskGaugingStation::REFERENCE_NAME,
    ];

    private Generator $generator;
    private GaugingStationProviderFactory $gaugingStationProviderFactory;

    public function __construct(\Faker\Generator $generator, GaugingStationProviderFactory $gaugingStationProviderFactory)
    {
        $this->generator = $generator;
        $this->gaugingStationProviderFactory = $gaugingStationProviderFactory;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::$gaugingStationReferences as $gaugingStationReference) {
            $gaugingStation = $this->getReference($gaugingStationReference);
            assert($gaugingStation instanceof GaugingStation);

            $gaugingStationProvider = $this->gaugingStationProviderFactory->createGaugingStationProviderForGaugingStation($gaugingStation);

            $this->generateRecords($gaugingStationProvider);
        }

        $manager->flush();
    }

    private function generateRecords(GaugingStationProvider $gaugingStationProvider): void
    {
        for ($subYear = 0; $subYear < self::MAX_YEARS_COUNT; $subYear++) {
            $recordingStartDay = Carbon::now()->subYears($subYear);

            if ($recordingStartDay->dayOfYear > self::MAX_RECORDS_IN_YEAR_BY_STATION) {
                $recordingStartDay->subDays(self::MAX_RECORDS_IN_YEAR_BY_STATION);
            } else {
                $recordingStartDay->startOfYear();
            }

            $this->generateRecordsFromStartDay($gaugingStationProvider, $recordingStartDay);
        }
    }

    private function generateRecordsFromStartDay(GaugingStationProvider $gaugingStationProvider, Carbon $recordingStartDay): void
    {
        $recordingEndDay = (clone $recordingStartDay)->addDays(self::MAX_RECORDS_IN_YEAR_BY_STATION - 1);
        $maxRecordingEndDay = min(Carbon::now(), $recordingEndDay);

        $recordsDatePeriod = new DatePeriod($recordingStartDay, new DateInterval('P1D'), $maxRecordingEndDay);

        foreach ($recordsDatePeriod as $recordRecordedAt) {
            $gaugingStationProvider->addRecord(
                Uuid::uuid4(),
                $recordRecordedAt,
                $this->generator->randomFloat(0, self::MIN_WATER_LEVEL, self::MAX_WATER_LEVEL),
                $this->generator->randomFloat(1, self::MIN_TEMPERATURE, self::MAX_TEMPERATURE)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadNovosibirskGaugingStation::class,
            LoadNovosibirskHydroelectricPowerStationGaugingStation::class,
            LoadBerdskGaugingStation::class,
            LoadHideBerdskGaugingStation::class,
            LoadHideNovosibirskGaugingStation::class,
            LoadHideGornoAltayskGaugingStation::class,
        ];
    }
}
