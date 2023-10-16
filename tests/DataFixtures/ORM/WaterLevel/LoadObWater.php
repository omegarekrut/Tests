<?php

namespace Tests\DataFixtures\ORM\WaterLevel;

use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Module\SlugGenerator\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadObWater extends Fixture
{
    public const REFERENCE_NAME = 'ob-river';

    private SlugGenerator $slugGenerator;

    public function __construct(SlugGenerator $slugGenerator)
    {
        $this->slugGenerator = $slugGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $id = Uuid::uuid4();
        $name = 'Обь';

        $water = new Water(
            $id,
            $this->slugGenerator->generate($name, Water::class),
            $name,
            WaterType::river()
        );

        $this->addReference(self::REFERENCE_NAME, $water);

        $manager->persist($water);
        $manager->flush();
    }
}
