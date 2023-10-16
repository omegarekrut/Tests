<?php

namespace Tests\DataFixtures\ORM\Record\News;

use App\Domain\Record\News\Entity\News;
use App\Module\Author\AuthorInterface;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadNewsWithComments extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'news-with-comments';

    private CommentHelper $commentHelper;
    private Generator $faker;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $faker, MediaHelper $mediaHelper, CommentHelper $commentHelper)
    {
        $this->faker = $faker;
        $this->mediaHelper = $mediaHelper;
        $this->commentHelper = $commentHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadTestUser::USER_TEST);
        assert($author instanceof AuthorInterface);

        $news = new News(
            'News with comments',
            $this->faker->realText(),
            $author,
            $this->faker->randomHtml(),
            $this->mediaHelper->createImage()
        );

        $news->updateCreatedAt(Carbon::now());

        $manager->persist($news);

        $this->commentHelper->addComments($this, $news);

        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $news);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
            LoadTestUser::class,
        ];
    }
}
