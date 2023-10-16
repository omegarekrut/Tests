<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;

class CompanyArticleFactory
{
    private Generator $faker;
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;

    public function __construct(Generator $faker, MediaHelper $mediaHelper, AuthorHelper $authorHelper)
    {
        $this->faker = $faker;
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
    }

    public function createPublicCompanyArticleForCompany(Company $company): CompanyArticle
    {
        $companyArticle = new CompanyArticle(
            $this->faker->realText(20),
            $this->faker->randomBBCode(),
            $this->authorHelper->createFromUser($company->getOwner()),
            $company,
            false,
            $this->faker->realText(255)
        );
        $companyArticle->addImage($this->mediaHelper->createImage());
        $companyArticle->addVideoUrl($this->faker->videoUrl());

        return $companyArticle;
    }

    public function createHiddenCompanyArticleForCompany(Company $company): CompanyArticle
    {
        $companyArticle = $this->createPublicCompanyArticleForCompany($company);
        $companyArticle->hide();

        return $companyArticle;
    }
}
