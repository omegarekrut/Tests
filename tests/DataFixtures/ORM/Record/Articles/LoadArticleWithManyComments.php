<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadArticleWithManyComments extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'article-with-many-comments';

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(
        \Faker\Generator $generator,
        AuthorHelper $authorHelper
    ) {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $article = new Article(
            self::REFERENCE_NAME,
            $this->generator->randomBBCode(),
            $this->authorHelper->chooseAuthor($this),
            $this->getReference(LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE),
            false,
            new ImageCollection([]),
            $this->generator->realText(255)
        );

        $manager->persist($article);

        for ($numberOfUser = 1; $numberOfUser < 11; ++$numberOfUser) {
            $article->addComment(
                Uuid::uuid4(),
                $this->generator->regexify('[A-Za-z0-9]{20}'),
                $this->generator->realText(),
                $this->getReference(LoadNumberedUsers::getReferenceNameByNumber($numberOfUser)),
            );
        }

        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $article);
    }

    /**
     * @return string[]
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
