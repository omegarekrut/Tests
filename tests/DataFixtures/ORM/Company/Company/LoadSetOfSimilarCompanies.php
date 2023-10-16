<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Entity\Rubric;
use App\Domain\Company\Entity\ValueObject\ContactDTO;
use App\Util\Coordinates\Coordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadPaidReservoirsRubric;
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;

class LoadSetOfSimilarCompanies extends Fixture implements DependentFixtureInterface
{
    public const ORIGINAL_COMPANY_REFERENCE = 'original-company';
    public const COMPANY_WITH_SAME_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE = 'company-with-same-coordinates-and-two-common-rubrics';
    public const COMPANY_WITH_SAME_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE = 'company-with-same-coordinates-and-one-common-rubric';
    public const COMPANY_WITH_DIFFERENT_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE = 'company-with-different-coordinates-and-two-common-rubrics';
    public const COMPANY_WITH_DIFFERENT_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE = 'company-with-different-coordinates-and-one-common-rubric';

    private CompanyFactory $companyFactory;
    private ContactDTOFakeFactory $contactDTOFakeFactory;

    private Rubric $tackleShopsRubric;
    private Rubric $paidReservoirsRubric;

    public function __construct(CompanyFactory $companyFactory, ContactDTOFakeFactory $contactDTOFakeFactory)
    {
        $this->companyFactory = $companyFactory;
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $this->tackleShopsRubric = $this->getReference(LoadTackleShopsRubric::REFERENCE_NAME);
        $this->paidReservoirsRubric = $this->getReference(LoadPaidReservoirsRubric::REFERENCE_NAME);

        $this->loadOriginalCompany($manager);
        $this->loadCompanyWithSameCoordinatesAndOneCommonRubric($manager);
        $this->loadCompanyWithSameCoordinatesAndTwoCommonRubrics($manager);
        $this->loadCompanyWithDifferentCoordinatesAndOneCommonRubric($manager);
        $this->loadCompanyWithDifferentCoordinatesAndTwoCommonRubrics($manager);

        $manager->flush();
    }

    private function loadOriginalCompany(ObjectManager $manager): void
    {
        $company = $this->createCompany(
            self::ORIGINAL_COMPANY_REFERENCE,
            new ArrayCollection([$this->tackleShopsRubric, $this->paidReservoirsRubric]),
        );

        $this->addReference(self::ORIGINAL_COMPANY_REFERENCE, $company);

        $manager->persist($company);

        $this->rewriteContactsFromDTO($company, new Coordinates(0.0, 0.0));
    }

    private function loadCompanyWithSameCoordinatesAndTwoCommonRubrics(ObjectManager $manager): void
    {
        $company = $this->createCompany(
            self::COMPANY_WITH_SAME_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE,
            new ArrayCollection([$this->tackleShopsRubric, $this->paidReservoirsRubric]),
        );

        $this->addReference(self::COMPANY_WITH_SAME_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE, $company);

        $manager->persist($company);

        $this->rewriteContactsFromDTO($company, new Coordinates(0.0, 0.0));
    }

    private function loadCompanyWithSameCoordinatesAndOneCommonRubric(ObjectManager $manager): void
    {
        $company = $this->createCompany(
            self::COMPANY_WITH_SAME_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE,
            new ArrayCollection([$this->tackleShopsRubric]),
        );

        $this->addReference(self::COMPANY_WITH_SAME_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE, $company);

        $manager->persist($company);

        $this->rewriteContactsFromDTO($company, new Coordinates(0.0, 0.0));
    }

    private function loadCompanyWithDifferentCoordinatesAndTwoCommonRubrics(ObjectManager $manager): void
    {
        $company = $this->createCompany(
            self::COMPANY_WITH_DIFFERENT_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE,
            new ArrayCollection([$this->tackleShopsRubric, $this->paidReservoirsRubric]),
        );

        $this->addReference(self::COMPANY_WITH_DIFFERENT_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE, $company);

        $manager->persist($company);

        $this->rewriteContactsFromDTO($company, new Coordinates(1.0, 1.0));
    }

    private function loadCompanyWithDifferentCoordinatesAndOneCommonRubric(ObjectManager $manager): void
    {
        $company = $this->createCompany(
            self::COMPANY_WITH_DIFFERENT_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE,
            new ArrayCollection([$this->tackleShopsRubric]),
        );

        $this->addReference(self::COMPANY_WITH_DIFFERENT_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE, $company);

        $manager->persist($company);

        $this->rewriteContactsFromDTO($company, new Coordinates(1.0, 1.0));
    }

    private function createCompany(string $name, ArrayCollection $rubrics): Company
    {
        return $this->companyFactory->createCompany($name, $rubrics);
    }

    private function rewriteContactsFromDTO(Company $company, Coordinates $coordinates): void
    {
        $company->rewriteContactsFromDTO(
            $this->createContactDTO($coordinates)
        );
    }

    private function createContactDTO(Coordinates $coordinates): ContactDTO
    {
        $contactDTO = $this->contactDTOFakeFactory->createFakeContactDTO();
        $contactDTO->locations = new LocationCollection([new Location(Uuid::uuid4(), clone $coordinates)]);

        return $contactDTO;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTackleShopsRubric::class,
            LoadPaidReservoirsRubric::class,
        ];
    }
}
