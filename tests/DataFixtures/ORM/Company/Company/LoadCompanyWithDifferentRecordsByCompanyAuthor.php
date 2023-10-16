<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Util\Security\AssertionSubject\OwnerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadCompanyWithDifferentRecordsByCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-different-records-by-company-author';

    private CompanyFactory $companyFactory;

    public function __construct(CompanyFactory $companyFactory)
    {
        $this->companyFactory = $companyFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $userWhoWillOwnCompany = $this->getReference(LoadTestUser::USER_TEST);
        assert($userWhoWillOwnCompany instanceof OwnerInterface);

        $employee = $this->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($employee instanceof User);

        $companyWithOwner = $this->createCompany();
        $companyWithOwner->setOwner($userWhoWillOwnCompany);
        $companyWithOwner->addEmployee($employee);

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
            LoadTestUser::class,
            LoadMostActiveUser::class,
            LoadDefaultRubric::class,
        ];
    }
}
