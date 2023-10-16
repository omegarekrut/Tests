<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Util\Coordinates\Coordinates;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateInterval;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class GaugingStationProviderFactory
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function createGaugingStationProviderForGaugingStation(GaugingStation $gaugingStation): GaugingStationProvider
    {
        $gaugingStationProvider = new GaugingStationProvider(
            Uuid::uuid4(),
            new ExternalIdentifier(GaugingStationRecordsProviderKey::esimo(), $this->generator->postcode, $this->generator->city),
            new GeographicalPosition(
                new Coordinates($this->generator->latitude, $this->generator->longitude),
                random_int(1000, 9999),
                random_int(1000, 9999),
                random_int(-100, 100)
            ),
            $this->generator->word,
        );

        $gaugingStationProvider->setGaugingStation($gaugingStation);
        $gaugingStation->addGaugingStationProvider($gaugingStationProvider);

        return $gaugingStationProvider;
    }

    public function createGaugingStationProviderForGaugingStationWithRecords(GaugingStation $gaugingStation, ?CarbonInterface $date = null): GaugingStationProvider
    {
        $date = $date ?? Carbon::today();

        $gaugingStationProvider = $this->createGaugingStationProviderForGaugingStation($gaugingStation);

        $count = random_int(1, 10);

        for ($i = 0; $i < $count; $i++) {
            $gaugingStationProvider->addRecord(
                Uuid::uuid4(),
                clone ($date)->sub(new DateInterval(sprintf('P%dD', $i + 1))),
                random_int(-10, 42),
                random_int(3, 24)
            );
        }

        $gaugingStation->rewriteFirstRecord($gaugingStationProvider->getRecords()->first());
        $gaugingStation->rewriteLatestRecord($gaugingStationProvider->getRecords()->last());

        return $gaugingStationProvider;
    }
}
