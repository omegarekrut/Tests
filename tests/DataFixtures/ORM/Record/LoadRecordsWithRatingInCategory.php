<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Video\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadRecordsWithRatingInCategory extends Fixture implements DependentFixtureInterface
{
    public const POSITIVE_RECORDS_COUNT = 3;
    public const NEGATIVE_RECORDS_COUNT = 2;

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;
    private MediaHelper $mediaHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::POSITIVE_RECORDS_COUNT; $i++) {
            $record = $this->createRecord();
            RatingHelper::setPositiveRating($record);

            $manager->persist($record);
        }

        for ($i = 1; $i <= self::NEGATIVE_RECORDS_COUNT; $i++) {
            $record = $this->createRecord();
            RatingHelper::setNegativeRating($record);

            $manager->persist($record);
        }

        $manager->flush();
    }

    private function createRecord(): Record
    {
        return new Video(
            $this->generator->realText(20),
            $this->mediaHelper->createVideo(),
            $this->authorHelper->chooseAuthor($this),
            $this->getReference(self::getReferenceCategory()),
            $this->generator->realText(),
            $this->generator->videoUrl(),
            $this->mediaHelper->createImage()
        );
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadMostActiveUser::class,
            LoadNumberedUsers::class,
        ];
    }

    public static function getReferenceCategory(): string
    {
        return LoadCategories::getReferenceRootName(LoadCategories::ROOT_VIDEO);
    }
}
