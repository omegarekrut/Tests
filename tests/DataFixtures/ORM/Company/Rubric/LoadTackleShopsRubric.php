<?php

namespace Tests\DataFixtures\ORM\Company\Rubric;

use App\Domain\Company\Entity\Rubric;
use App\Module\SlugGenerator\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Ramsey\Uuid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadTackleShopsRubric extends Fixture implements SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'rubric-tackle-shops';

    public const PRIORITY_INDEX = 50;

    private SlugGenerator $slugGenerator;

    public function __construct(SlugGenerator $slugGenerator)
    {
        $this->slugGenerator = $slugGenerator;
    }

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function load(ObjectManager $manager): void
    {
        $name = 'Магазин снастей';
        $rubric = new Rubric(Uuid::uuid4(), $this->slugGenerator->generate($name, Rubric::class), $name, self::PRIORITY_INDEX);

        $this->addReference(self::REFERENCE_NAME, $rubric);

        $manager->persist($rubric);
        $manager->flush();
    }
}
