<?php

namespace Tests\DataFixtures\ORM\Company\CompanyWithSubscription;

use App\Domain\Company\Entity\Company;
use App\Util\Security\AssertionSubject\OwnerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\CompanySubscriptionFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadCompanyWithActiveSubscriptionWithUser extends Fixture implements SingleReferenceFixtureInterface, DependentFixtureInterface
{
    private const REFERENCE_NAME = 'company-with-active-subscription-with-user';

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
        $company = $this->createCompany();

        $company->addSubscription(
            $this->companySubscriptionFactory->createMonthlySubscription()
        );

        $owner = $this->getReference(LoadTestUser::USER_TEST);
        assert($owner instanceof OwnerInterface);

        $company->setOwner($owner);

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
            LoadDefaultRubric::class,
            LoadTestUser::class,
        ];
    }
}
