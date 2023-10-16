<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Record\Article\Entity\Article;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadArticleWithComment extends Fixture implements DependentFixtureInterface, SingleReferenceFixtureInterface
{
    private Generator $generator;

    public static function getReferenceName(): string
    {
        return 'article-with-comment';
    }

    public function __construct(
        Generator $generator
    ) {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $articleAuthor = $this->getReference(LoadTestUser::getReferenceName());
        assert($articleAuthor instanceof AuthorInterface);

        $article = new Article(
            static::getReferenceName(),
            $this->generator->randomBBCode(),
            $articleAuthor,
            $this->getReference(LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE),
            false,
            new ImageCollection([]),
            $this->generator->realText(255)
        );

        $manager->persist($article);

        $commentAuthor = $this->getReference(LoadMostActiveUser::getReferenceName());
        assert($commentAuthor instanceof AuthorInterface);

        $article->addComment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            $this->generator->realText(),
            $commentAuthor,
        );

        $manager->flush();

        $this->addReference(self::getReferenceName(), $article);
    }

    /**
     * @return string[]
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
