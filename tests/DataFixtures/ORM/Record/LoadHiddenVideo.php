<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Video\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadHiddenVideo extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'hidden-video';

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
        $video = new Video(
            $this->generator->realText(20),
            $this->mediaHelper->createVideo(),
            $this->authorHelper->chooseAuthor($this),
            $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO)),
            $this->generator->realText(),
            $this->generator->videoUrl(),
            $this->mediaHelper->createImage()
        );

        $video->hide();

        $manager->persist($video);
        $this->addReference(self::REFERENCE_NAME, $video);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
