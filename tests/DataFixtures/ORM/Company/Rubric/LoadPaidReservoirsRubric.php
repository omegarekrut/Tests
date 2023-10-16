<?php

namespace Tests\DataFixtures\ORM\Company\Rubric;

use App\Domain\Company\Entity\Rubric;
use App\Module\SlugGenerator\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Ramsey\Uuid\Uuid;
use Doctrine\Persistence\ObjectManager;

class LoadPaidReservoirsRubric extends Fixture
{
    public const REFERENCE_NAME = 'rubric-paid-reservoirs';

    private SlugGenerator $slugGenerator;

    public function __construct(SlugGenerator $slugGenerator)
    {
        $this->slugGenerator = $slugGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $name = 'Платные водоемы';
        $rubric = new Rubric(Uuid::uuid4(), $this->slugGenerator->generate($name, Rubric::class), $name);

        $this->addReference(self::REFERENCE_NAME, $rubric);

        $manager->persist($rubric);
        $manager->flush();
    }
}
