<?php

namespace Tests\DataFixtures\ORM\Company\CompanyWithSubscription;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\CompanySubscriptionFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadCompanyWithPremiumSubscription extends Fixture implements SingleReferenceFixtureInterface, DependentFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'company-with-premium-subscription';

    private CompanyFactory $companyFactory;
    private CompanySubscriptionFactory $companySubscriptionFactory;

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function __construct(CompanyFactory $companyFactory, CompanySubscriptionFactory $companySubscriptionFactory)
    {
        $this->companyFactory = $companyFactory;
        $this->companySubscriptionFactory = $companySubscriptionFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $userTest = $this->getReference(LoadTestUser::getReferenceName());
        assert($userTest instanceof User);

        $company = $this->createCompany();

        $company->setOwner($userTest);
        $company->addSubscription(
            $this->companySubscriptionFactory->createMonthlyPremiumSubscription()
        );

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::getReferenceName(), $company);
    }

    private function createCompany(): Company
    {
        $name = self::getReferenceName();
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
            LoadTestUser::class,
            LoadDefaultRubric::class,
        ];
    }
}
