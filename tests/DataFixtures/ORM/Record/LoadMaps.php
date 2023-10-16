<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Map\Entity\Map;
use App\Util\Coordinates\Coordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadMaps extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'map';
    public const COUNT = 30;

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', static::REFERENCE_PREFIX, rand(1, static::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $map = new Map(
                $this->generator->realText(20),
                $this->generator->realText(),
                $this->authorHelper->chooseAuthor($this),
                new Coordinates($this->generator->latitude, $this->generator->longitude)
            );

            $manager->persist($map);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $map);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
