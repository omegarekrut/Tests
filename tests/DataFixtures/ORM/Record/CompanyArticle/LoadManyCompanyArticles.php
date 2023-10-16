<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyArticleFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadManySimpleOwnedCompanies;

class LoadManyCompanyArticles extends Fixture implements FixtureInterface, DependentFixtureInterface
{
    public const COMPANY_PREFIX_REFERENCE = 'simple-company-article';

    private CompanyArticleFactory $companyArticleFactory;

    public function __construct(CompanyArticleFactory $companyArticleFactory)
    {
        $this->companyArticleFactory = $companyArticleFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadSimpleCompanyArticles($manager);

        $manager->flush();
    }

    private function loadSimpleCompanyArticles(ObjectManager $manager): void
    {
        foreach (range(0, LoadManySimpleOwnedCompanies::AMOUNT_OF_COMPANIES_TO_LOAD) as $companyArticleIndex) {
            /** @var Company $company */
            $company = $this->getReference(
                LoadManySimpleOwnedCompanies::COMPANY_PREFIX_REFERENCE.'-'.$companyArticleIndex
            );
            $companyArticle = $this->companyArticleFactory->createPublicCompanyArticleForCompany($company);
            $manager->persist($companyArticle);
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [LoadManySimpleOwnedCompanies::class];
    }
}
