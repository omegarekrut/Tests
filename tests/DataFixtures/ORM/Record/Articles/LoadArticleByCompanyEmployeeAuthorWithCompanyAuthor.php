<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithEmployee;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadUserWhichCompanyEmployee;

class LoadArticleByCompanyEmployeeAuthorWithCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'article-by-company-employee-author-with-company-author';

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithEmployee::REFERENCE_NAME);
        assert($company instanceof Company);

        $author = $this->getReference(LoadUserWhichCompanyEmployee::REFERENCE_NAME);
        assert($author instanceof User);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));

        $article = new Article(
            self::REFERENCE_NAME,
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
            $author,
            $category,
            false,
            new ImageCollection(),
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.'
        );
        $article->setCompanyAuthor($company);

        $manager->persist($article);

        $this->addReference(self::REFERENCE_NAME, $article);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadCompanyWithEmployee::class,
            LoadUserWhichCompanyEmployee::class,
            LoadCategories::class,
        ];
    }
}
