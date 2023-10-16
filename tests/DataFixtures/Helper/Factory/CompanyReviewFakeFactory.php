<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Module\Author\AuthorInterface;

class CompanyReviewFakeFactory
{
    public function createFake(Company $company, AuthorInterface $author, string $text): CompanyReview
    {
        return new CompanyReview(
            $this->createTitle($author->getUsername(), $company->getName()),
            $text,
            $author,
            $company
        );
    }

    private function createTitle(string $authorName, string $companyName): string
    {
        return sprintf('Отзыв пользователя %s на компанию %s', $authorName, $companyName);
    }
}
