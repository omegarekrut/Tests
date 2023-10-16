<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;

class LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-without-owner-to-future-approve-ownership-request';

    private CompanyFactory $companyFactory;

    public function __construct(CompanyFactory $companyFactory)
    {
        $this->companyFactory = $companyFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->createCompany();

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);
    }

    private function createCompany(): Company
    {
        $name = 'Рыбацкая хижина';
        $rubric = $this->getReference(LoadDefaultRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadDefaultRubric::class,
        ];
    }
}
