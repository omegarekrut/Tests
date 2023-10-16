<?php

namespace Tests\DataFixtures\ORM\Company\Rubric;

use App\Domain\Company\Entity\Rubric;
use App\Module\SlugGenerator\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Ramsey\Uuid\Uuid;
use Doctrine\Persistence\ObjectManager;

class LoadAquaMotorcycleShopsRubric extends Fixture
{
    public const REFERENCE_NAME = 'aqua-motorcycle-shops';

    public const PRIORITY_INDEX = 200;

    private SlugGenerator $slugGenerator;

    public function __construct(SlugGenerator $slugGenerator)
    {
        $this->slugGenerator = $slugGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $name = 'Магазин аква/мототехники';
        $rubric = new Rubric(Uuid::uuid4(), $this->slugGenerator->generate($name, Rubric::class), $name, self::PRIORITY_INDEX);

        $this->addReference(self::REFERENCE_NAME, $rubric);

        $manager->persist($rubric);
        $manager->flush();
    }
}
