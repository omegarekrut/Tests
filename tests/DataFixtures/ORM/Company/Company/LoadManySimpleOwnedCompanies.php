<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Rubric;
use App\Domain\User\Entity\User;
use App\Util\Coordinates\Coordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadPaidReservoirsRubric;
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;
use Tests\DataFixtures\ORM\User\LoadManySimpleUsersForOwnCompany;

class LoadManySimpleOwnedCompanies extends Fixture implements DependentFixtureInterface
{
    public const COMPANY_PREFIX_REFERENCE = 'simple-company';
    public const AMOUNT_OF_COMPANIES_TO_LOAD = LoadManySimpleUsersForOwnCompany::COUNT_OF_USERS_FOR_LOAD;

    private Rubric $tackleShopsRubric;
    private Rubric $paidReservoirsRubric;
    private CompanyFactory $companyFactory;

    public function __construct(CompanyFactory $companyFactory)
    {
        $this->companyFactory = $companyFactory;
    }

    public static function getRandomReferenceName(): string
    {
        $randomIndex = rand(0, self::AMOUNT_OF_COMPANIES_TO_LOAD - 1);

        return self::COMPANY_PREFIX_REFERENCE.'-'.$randomIndex;
    }

    public function load(ObjectManager $manager): void
    {
        $this->tackleShopsRubric = $this->getReference(LoadTackleShopsRubric::REFERENCE_NAME);
        $this->paidReservoirsRubric = $this->getReference(LoadPaidReservoirsRubric::REFERENCE_NAME);

        $this->loadSimpleCompanies($manager);

        $manager->flush();
    }

    private function loadSimpleCompanies(ObjectManager $manager): void
    {
        foreach (range(0, self::AMOUNT_OF_COMPANIES_TO_LOAD) as $companyIndex) {
            $companyName = self::COMPANY_PREFIX_REFERENCE.'-'.$companyIndex;

            $company = $this->companyFactory->createCompanyWithCoordinatesLocation(
                $companyName,
                new ArrayCollection([$this->tackleShopsRubric, $this->paidReservoirsRubric]),
                new Coordinates(0.0, 0.0),
            );

            /** @var User $owner  */
            $owner = $this->getReference(LoadManySimpleUsersForOwnCompany::USER_PREFIX_REFERENCE.'-'.$companyIndex);

            $company->setOwner($owner);

            $this->addReference($companyName, $company);
            $manager->persist($company);
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTackleShopsRubric::class,
            LoadPaidReservoirsRubric::class,
            LoadManySimpleUsersForOwnCompany::class,
        ];
    }
}
