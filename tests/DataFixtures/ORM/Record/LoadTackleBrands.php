<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Tackle\Entity\TackleBrand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTackleBrands extends Fixture
{
    private const REFERENCE_PREFIX = 'tackle-brand';
    private const COUNT = 30;

    private \Faker\Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $brand = new TackleBrand($this->generator->realText(25));

            $manager->persist($brand);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $brand);
        }

        $manager->flush();
    }
}
