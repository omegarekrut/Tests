<?php

namespace Tests\DataFixtures\ORM\Draft;

use App\Domain\Draft\Entity\Draft;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;

class LoadDraft extends Fixture
{
    private const REFERENCE_PREFIX = 'draft';
    private const COUNT = 4;

    private \Faker\Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $page = new Draft(
                $this->generator->title,
                $this->generator->randomHtml()
            );

            $manager->persist($page);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $page);
        }
        $manager->flush();
    }
}
