<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithDifferentRecordsByCompanyAuthor;
use Tests\DataFixtures\ORM\LoadCategories;

class LoadArticleByCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'article-by-company-author';

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithDifferentRecordsByCompanyAuthor::REFERENCE_NAME);
        assert($company instanceof Company);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
        assert($category instanceof Category);

        $article = new Article(
            self::REFERENCE_NAME,
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
            $company->getOwner(),
            $category,
            false,
            new ImageCollection(),
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.'
        );

        $article->setCompanyAuthor($company);

        $manager->persist($article);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $article);
    }

    public function getDependencies(): array
    {
        return [
            LoadCompanyWithDifferentRecordsByCompanyAuthor::class,
            LoadCategories::class,
        ];
    }
}
