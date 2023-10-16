<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadArticleForSemanticLinksForRecommendedRecordForDeletingAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'article-for-recommended-record-for-deleting-author';

    private AuthorHelper $authorHelper;

    public function __construct(AuthorHelper $authorHelper)
    {
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $article = new Article(
            self::REFERENCE_NAME,
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
            $this->authorHelper->chooseAuthor($this),
            $this->getReference($this->getReferenceNameForRootCategory()),
            false,
            new ImageCollection(),
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.'
        );

        $manager->persist($article);

        $this->addReference(self::REFERENCE_NAME, $article);

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
        if (random_int(0, 1) === 0) {
            return LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES);
        }

        return LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE;
    }
}
