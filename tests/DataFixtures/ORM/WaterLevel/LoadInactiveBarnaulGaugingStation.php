<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\Water;
use App\Module\ShortUuid\ShortUuidConverterInterface;
use App\Module\SlugGenerator\SlugGenerator;
use App\Util\Coordinates\Coordinates;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\GaugingStationProviderFactory;

class LoadInactiveBarnaulGaugingStation extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'barnaul';

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
        $water = $this->getReference(LoadObWater::REFERENCE_NAME);
        assert($water instanceof Water);

        $coordinates = new Coordinates(53.346785, 83.776856);
        $geographicalPosition = new GeographicalPosition($coordinates, 60, 140, 112);

        $id = Uuid::uuid4();
        $name = 'Барнаул';

        $externalIdentifier = new ExternalIdentifier(GaugingStationRecordsProviderKey::meteoNso(), '10006', $name);
        $gaugingStation = new GaugingStation(
            $id,
            $this->shortUuidConverter->encode($id),
            $this->slugGenerator->generate($name, GaugingStation::class),
            $water,
            $name,
            $geographicalPosition,
            $externalIdentifier
        );

        $recordingStartDay = Carbon::now()->subDays(GaugingStation::NUMBER_OF_DAYS_TO_DEFINE_INACTIVE_RECORD);

        $this->gaugingStationProviderFactory->createGaugingStationProviderForGaugingStationWithRecords($gaugingStation, $recordingStartDay);

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
            LoadObWater::class,
        ];
    }
}
