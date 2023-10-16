<?php

namespace Tests\DataFixtures\ORM\Record\Video;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Video\Entity\Video;
use App\Util\ImageStorage\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithDifferentRecordsByCompanyAuthor;
use Tests\DataFixtures\ORM\LoadCategories;

class LoadVideoByCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'video-by-company-author';

    private Generator $generator;
    private MediaHelper $mediaHelper;

    public function __construct(
        Generator $generator,
        MediaHelper $mediaHelper
    ) {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithDifferentRecordsByCompanyAuthor::REFERENCE_NAME);
        assert($company instanceof Company);

        $text = 'text for video';

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO));
        assert($category instanceof Category);

        $video = new Video(
            $this->generator->realText(20),
            $this->mediaHelper->createVideo(),
            $company->getOwner(),
            $category,
            $text,
            $this->generator->videoUrl(),
            $this->mediaHelper->createImage()
        );

        $video->setCompanyAuthor($company);

        $manager->persist($video);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $video);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadCompanyWithDifferentRecordsByCompanyAuthor::class,
        ];
    }
}
