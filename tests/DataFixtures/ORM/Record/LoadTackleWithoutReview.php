<?php

namespace Tests\DataFixtures\ORM\Record;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\TackleFakeFactory;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadTackleWithoutReview extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'tackle-without-review';
    private const COUNT = 10;

    private TackleFakeFactory $tackleFakeFactory;

    public function __construct(TackleFakeFactory $tackleFakeFactory)
    {
        $this->tackleFakeFactory = $tackleFakeFactory;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $tackle = $this->tackleFakeFactory->createFake($this);
            $tackle->markRssExportStatusAsDisallowed();

            RatingHelper::setRating($tackle);

            $manager->persist($tackle);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $tackle);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadTackleBrands::class,
            LoadCategories::class,
            LoadMostActiveUser::class,
            LoadNumberedUsers::class,
        ];
    }
}
