<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\Water;
use App\Module\ShortUuid\ShortUuidConverterInterface;
use App\Module\SlugGenerator\SlugGenerator;
use App\Util\Coordinates\Coordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\GaugingStationProviderFactory;

class LoadBerdskGaugingStation extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'berdsk';
    private SlugGenerator $slugGenerator;
    private ShortUuidConverterInterface $shortUuidConverter;
    private GaugingStationProviderFactory $gaugingStationProviderFactory;

    public function __construct(SlugGenerator $slugGenerator, ShortUuidConverterInterface $shortUuidConverter, GaugingStationProviderFactory $gaugingStationProviderFactory)
    {
        $this->slugGenerator = $slugGenerator;
        $this->shortUuidConverter = $shortUuidConverter;
        $this->gaugingStationProviderFactory = $gaugingStationProviderFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $water = $this->getReference(LoadBerdWater::REFERENCE_NAME);
        assert($water instanceof Water);

        $coordinates = new Coordinates(53.346785, 83.776856);
        $geographicalPosition = new GeographicalPosition($coordinates, 100, 100, 110);

        $id = Uuid::uuid4();
        $name = 'Бердск';

        $gaugingStation = new GaugingStation(
            $id,
            $this->shortUuidConverter->encode($id),
            $this->slugGenerator->generate($name, GaugingStation::class),
            $water,
            $name,
            $geographicalPosition,
        );

        $this->gaugingStationProviderFactory->createGaugingStationProviderForGaugingStationWithRecords($gaugingStation);

        $this->addReference(self::REFERENCE_NAME, $gaugingStation);

        $manager->persist($gaugingStation);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadBerdWater::class,
        ];
    }
}
