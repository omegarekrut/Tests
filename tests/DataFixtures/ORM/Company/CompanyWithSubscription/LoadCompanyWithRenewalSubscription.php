<?php

namespace Tests\DataFixtures\ORM\Company\CompanyWithSubscription;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\CompanySubscriptionFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;

class LoadCompanyWithRenewalSubscription extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-renewal-subscription';

    private CompanyFactory $companyFactory;
    private CompanySubscriptionFactory $companySubscriptionFactory;

    public function __construct(CompanyFactory $companyFactory, CompanySubscriptionFactory $companySubscriptionFactory)
    {
        $this->companyFactory = $companyFactory;
        $this->companySubscriptionFactory = $companySubscriptionFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $companyWithOwner = $this->createCompany();

        $activeSubscription = $this->companySubscriptionFactory->createMonthlySubscription();

        $companyWithOwner->addSubscription($activeSubscription);
        $companyWithOwner->addSubscription(
            $this->companySubscriptionFactory->createRenewalSubscription($activeSubscription)
        );

        $manager->persist($companyWithOwner);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $companyWithOwner);
    }

    private function createCompany(): Company
    {
        $name = self::REFERENCE_NAME;
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
