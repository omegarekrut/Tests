<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyArticleFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;

class LoadHiddenCompanyArticle extends Fixture implements FixtureInterface, DependentFixtureInterface
{
    private const REFERENCE_NAME = 'hidden-company-article';
    private CompanyArticleFactory $companyArticleFactory;

    public function __construct(CompanyArticleFactory $companyArticleFactory)
    {
        $this->companyArticleFactory = $companyArticleFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $companyArticle = $this->companyArticleFactory->createHiddenCompanyArticleForCompany($company);

        $manager->persist($companyArticle);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $companyArticle);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [LoadAquaMotorcycleShopsCompany::class];
    }
}
