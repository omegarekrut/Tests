<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class LoadSimpleArticle extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'simple-article';

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($author instanceof User);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
        assert($category instanceof Category);

        $article = new Article(
            self::REFERENCE_NAME,
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
            $author,
            $category,
            false,
            new ImageCollection(),
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.'
        );

        $manager->persist($article);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $article);
    }

    public function getDependencies(): array
    {
        return [
            LoadMostActiveUser::class,
            LoadCategories::class,
        ];
    }
}
