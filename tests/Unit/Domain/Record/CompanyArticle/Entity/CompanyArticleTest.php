<?php

namespace Tests\Unit\Domain\User\Entity;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\Exception\ChangeCompanyArticleAuthorException;
use App\Module\Author\AuthorInterface;
use Tests\Unit\TestCase;

class CompanyArticleTest extends TestCase
{
    private function createCompanyArticle(): CompanyArticle
    {
        return new CompanyArticle(
            'some title',
            'some title',
            $this->createMock(AuthorInterface::class),
            $this->createMock(Company::class),
            false
        );
    }

    public function testRewriteCompanyArticleAuthor(): void
    {
        $this->expectException(ChangeCompanyArticleAuthorException::class);

        $companyArticle = $this->createCompanyArticle();

        $companyArticle->rewriteCompanyAuthor($this->createMock(Company::class));
    }

    public function testRemoveCompanyArticleAuthor(): void
    {
        $this->expectException(ChangeCompanyArticleAuthorException::class);

        $companyArticle = $this->createCompanyArticle();

        $companyArticle->clearCompanyAuthorInfo();
    }
}
