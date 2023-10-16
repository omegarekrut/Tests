<?php

namespace Tests\DataFixtures\ORM\Record\Video;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadVideoWithComment extends Fixture implements DependentFixtureInterface, SingleReferenceFixtureInterface
{
    private Generator $generator;
    private MediaHelper $mediaHelper;

    public static function getReferenceName(): string
    {
        return 'video-with-comment';
    }

    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $videoAuthor = $this->getReference(LoadTestUser::getReferenceName());
        assert($videoAuthor instanceof User);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO));
        assert($category instanceof Category);

        $video = new Video(
            'video with comment',
            $this->mediaHelper->createVideo(),
            $videoAuthor,
            $category,
            'video with comment text',
            $this->generator->videoUrl(),
            $this->mediaHelper->createImage()
        );

        $manager->persist($video);

        $commentAuthor = $this->getReference(LoadMostActiveUser::getReferenceName());
        assert($commentAuthor instanceof AuthorInterface);

        $video->addComment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            $this->generator->realText(),
            $commentAuthor,
        );

        $manager->flush();

        $this->addReference(self::getReferenceName(), $video);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadMostActiveUser::class,
            LoadTestUser::class,
        ];
    }
}
