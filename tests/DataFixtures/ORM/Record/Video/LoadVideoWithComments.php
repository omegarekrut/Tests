<?php

namespace Tests\DataFixtures\ORM\Record\Video;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadVideoWithComments extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'video-with-comments';

    private CommentHelper $commentHelper;
    private Generator $generator;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $generator, MediaHelper $mediaHelper, CommentHelper $commentHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
        $this->commentHelper = $commentHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($author instanceof User);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO));
        assert($category instanceof Category);

        $video = new Video(
            'video with comments',
            $this->mediaHelper->createVideo(),
            $author,
            $category,
            'video with comments text',
            $this->generator->videoUrl(),
            $this->mediaHelper->createImage()
        );

        $manager->persist($video);

        $this->commentHelper->addComments($this, $video);

        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $video);
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
