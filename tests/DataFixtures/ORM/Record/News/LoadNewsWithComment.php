<?php

namespace Tests\DataFixtures\ORM\Record\News;

use App\Domain\Record\News\Entity\News;
use App\Module\Author\AuthorInterface;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadNewsWithComment extends Fixture implements DependentFixtureInterface, SingleReferenceFixtureInterface
{
    private Generator $faker;
    private MediaHelper $mediaHelper;

    public static function getReferenceName(): string
    {
        return 'news-with-comment';
    }

    public function __construct(Generator $faker, MediaHelper $mediaHelper)
    {
        $this->faker = $faker;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $newsAuthor = $this->getReference(LoadTestUser::getReferenceName());
        assert($newsAuthor instanceof AuthorInterface);

        $news = new News(
            'News with comment',
            $this->faker->realText(),
            $newsAuthor,
            $this->faker->randomHtml(),
            $this->mediaHelper->createImage()
        );

        $news->updateCreatedAt(Carbon::now());

        $manager->persist($news);

        $commentAuthor = $this->getReference(LoadMostActiveUser::getReferenceName());
        assert($commentAuthor instanceof AuthorInterface);

        $news->addComment(
            Uuid::uuid4(),
            $this->faker->regexify('[A-Za-z0-9]{20}'),
            $this->faker->realText(),
            $commentAuthor,
        );

        $manager->flush();

        $this->addReference(self::getReferenceName(), $news);
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
