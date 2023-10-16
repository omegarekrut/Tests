<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadArticles extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_ARTICLE_FOR_BEST_COMMENTS = 'article-for-best-comments';

    private const REFERENCE_PREFIX = 'article';
    private const COUNT = 60;
    public const FULL_COUNT = 61;

    private \Faker\Generator $generator;
    private CommentHelper $commentHelper;
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, CommentHelper $commentHelper, MediaHelper $mediaHelper, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->commentHelper = $commentHelper;
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $article = new Article(
                $this->generator->realText(20),
                $this->generator->randomBBCodeWithMoreHeaders(),
                $this->authorHelper->chooseAuthor($this),
                $this->getReference($this->getReferenceNameForRootCategory()),
                false,
                new ImageCollection([
                    $this->mediaHelper->createImage(),
                    $this->mediaHelper->createImage(),
                ]),
                $this->generator->realText(255)
            );

            RatingHelper::setRating($article);
            $this->commentHelper->addComments($this, $article);

            $manager->persist($article);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $article);
        }

        $this->createArticleForBestComments($manager);

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadNumberedUsers::class,
            LoadModeratorAdvancedUser::class,
            LoadMostActiveUser::class,
        ];
    }

    private function getReferenceNameForRootCategory(): string
    {
        return rand(0, 1) === 0 ? LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES) : LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE;
    }

    private function createArticleForBestComments(ObjectManager $manager): void
    {
        $article = new Article(
            self::REFERENCE_ARTICLE_FOR_BEST_COMMENTS,
            $this->generator->randomBBCode(),
            $this->authorHelper->chooseAuthor($this),
            $this->getReference($this->getReferenceNameForRootCategory()),
            false,
            new ImageCollection([]),
            $this->generator->realText(255)
        );

        $manager->persist($article);

        for ($i = 0; $i < 10; ++$i) {
            $article->addComment(
                Uuid::uuid4(),
                $this->generator->regexify('[A-Za-z0-9]{20}'),
                $this->generator->realText(),
                $this->authorHelper->chooseAuthor($this)
            );
        }

        foreach ($article->getComments() as $comment) {
            $highRating = new RatingInfo(10, 10, 0, 10);
            $comment->updateRatingInfo($highRating);
        }

        $this->addReference(self::REFERENCE_ARTICLE_FOR_BEST_COMMENTS, $article);
    }
}
