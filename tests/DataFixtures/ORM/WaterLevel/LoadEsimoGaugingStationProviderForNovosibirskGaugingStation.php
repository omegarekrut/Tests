<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Util\Coordinates\Coordinates;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadEsimoGaugingStationProviderForNovosibirskGaugingStation extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'esimo-gauging-station-provider-for-novosibirsk-gaugingStation';

    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $gaugingStation = $this->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

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

        $gaugingStationProvider->addRecord(Uuid::uuid4(), new DateTime(), 42, 24);

        $this->addReference(self::REFERENCE_NAME, $gaugingStationProvider);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadNovosibirskGaugingStation::class,
        ];
    }
}
