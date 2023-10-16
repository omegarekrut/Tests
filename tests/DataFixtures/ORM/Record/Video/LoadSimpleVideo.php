<?php

namespace Tests\DataFixtures\ORM\Record\Video;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class LoadSimpleVideo extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'simple-video';

    private Generator $generator;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($author instanceof User);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO));
        assert($category instanceof Category);

        $video = new Video(
            'simple video',
            $this->mediaHelper->createVideo(),
            $author,
            $category,
            'simple video text',
            $this->generator->videoUrl(),
            $this->mediaHelper->createImage()
        );

        $manager->persist($video);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $video);
    }

    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadMostActiveUser::class,
        ];
    }
}
