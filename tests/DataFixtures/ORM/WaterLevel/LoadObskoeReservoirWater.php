<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Module\SlugGenerator\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadObskoeReservoirWater extends Fixture
{
    public const REFERENCE_NAME = 'obskoe-reservoir';

    private SlugGenerator $slugGenerator;

    public function __construct(SlugGenerator $slugGenerator)
    {
        $this->slugGenerator = $slugGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $id = Uuid::uuid4();
        $name = 'Обское водохранилище';

        $water = new Water(
            $id,
            $this->slugGenerator->generate($name, Water::class),
            $name,
            WaterType::reservoir()
        );

        $this->addReference(self::REFERENCE_NAME, $water);

        $manager->persist($water);
        $manager->flush();
    }
}
