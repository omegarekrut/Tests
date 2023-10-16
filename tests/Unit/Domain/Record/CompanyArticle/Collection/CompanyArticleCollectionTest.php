<?php

namespace Tests\Unit\Domain\Record\CompanyArticle\Collection;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class CompanyArticleCollectionTest extends TestCase
{
    public function testCompanyArticleCollectionCantBeCreatedWithNotCompanyArticleElement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Collection must contain only %s', CompanyArticle::class)
        );

        $notCompanyArticleArray = [$this->createMock(Article::class)];
        new CompanyArticleCollection($notCompanyArticleArray);
    }

    public function testCompanyArticleCollectionCanBeCreatedWithCompanyArticleElement(): void
    {
        $companyArticle = $this->createMock(CompanyArticle::class);

        $companyArticleCollection = new CompanyArticleCollection([$companyArticle]);

        $this->assertContains($companyArticle, $companyArticleCollection);
    }

    public function testCanAddCompanyArticleToCompanyArticleCollection(): void
    {
        $companyArticle = $this->createMock(CompanyArticle::class);

        $companyArticleCollection = new CompanyArticleCollection();
        $companyArticleCollection->add($companyArticle);

        $this->assertContains($companyArticle, $companyArticleCollection);
    }

    public function testCantAddNotCompanyArticleToCompanyArticleCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Collection element must be instance of %s', CompanyArticle::class)
        );

        $notCompanyArticle = $this->createMock(Article::class);

        $companyArticleCollection = new CompanyArticleCollection();
        $companyArticleCollection->add($notCompanyArticle);
    }

    public function testCanSetCompanyArticleToCompanyArticleCollection(): void
    {
        $companyArticle = $this->createMock(CompanyArticle::class);

        $companyArticleCollection = new CompanyArticleCollection();
        $companyArticleCollection->set(1, $companyArticle);

        $this->assertContains($companyArticle, $companyArticleCollection);
    }

    public function testCantSetNotCompanyArticleToCompanyArticleCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Collection element must be instance of %s', CompanyArticle::class)
        );

        $notCompanyArticle = $this->createMock(Article::class);

        $companyArticleCollection = new CompanyArticleCollection();
        $companyArticleCollection->set(1, $notCompanyArticle);
    }
}
