<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Util\Coordinates\Coordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadGaugingStationProviderWithoutGaugingStation extends Fixture
{
    public const REFERENCE_NAME = 'gauging-station-provider-without-gauging-station';

    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $gaugingStationProviderWithoutGaugingStation = new GaugingStationProvider(
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

        $this->addReference(self::REFERENCE_NAME, $gaugingStationProviderWithoutGaugingStation);

        $manager->persist($gaugingStationProviderWithoutGaugingStation);
        $manager->flush();
    }
}
