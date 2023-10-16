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

class LoadHideGornoAltayskGaugingStation extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'gorno-altaysk-hide';

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
        $water = $this->getReference(LoadKatunWater::REFERENCE_NAME);
        assert($water instanceof Water);

        $coordinates = new Coordinates(54.775467, 83.093632);
        $geographicalPosition = new GeographicalPosition($coordinates, 110, 110, 120);

        $id = Uuid::uuid4();
        $name = 'Горно-Алтайск';

        $gaugingStation = new GaugingStation(
            $id,
            $this->shortUuidConverter->encode($id),
            $this->slugGenerator->generate($name, GaugingStation::class),
            $water,
            $name,
            $geographicalPosition,
        );
        $gaugingStation->hide();

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
            LoadKatunWater::class,
        ];
    }
}
