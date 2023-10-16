<?php

namespace Tests\DataFixtures\ORM\Record\Video;

use App\Domain\Record\Video\Entity\Video;
use App\Util\ImageStorage\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadVideoWithDisallowedCommentsAccess extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'video-with-disallowed-comments-access';

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
        $text = 'text for video';

        $video = new Video(
            $this->generator->realText(20),
            $this->mediaHelper->createVideo(),
            $this->authorHelper->chooseAuthor($this),
            $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO)),
            $text,
            $this->generator->videoUrl(),
            new Image('')
        );

        $video->disallowComments();

        $manager->persist($video);
        $this->addReference(self::REFERENCE_NAME, $video);

        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
