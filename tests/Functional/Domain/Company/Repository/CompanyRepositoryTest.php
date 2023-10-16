<?php

namespace Tests\Functional\Domain\Company\Repository;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Company\Search\CompanySearchData;
use App\Domain\Region\Entity\Region;
use App\Module\Author\AnonymousAuthor;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyFromNovosibirskRegion;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithCustomDescription;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyFromNovosibirskRegionWithDeliveryToRegionsAvailable;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithIrkutskRegion;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest;
use Tests\DataFixtures\ORM\Company\Company\LoadHiddenCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithRegion;
use Tests\DataFixtures\ORM\Company\Company\LoadOldCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadSetOfSimilarCompanies;
use Tests\DataFixtures\ORM\Company\Company\LoadSimilarCompanyNearbyHiddenCompany;
use Tests\DataFixtures\ORM\Company\Contact\LoadAquaMotorcycleShopsContact;
use Tests\DataFixtures\ORM\Company\Contact\LoadPaidReservoirsContact;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;
use Tests\DataFixtures\ORM\Region\Region\LoadIrkutskRegion;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadPaidReservoirsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;

/**
 * @group company
 * @group company-create
 */
class CompanyRepositoryTest extends TestCase
{
    private ?CompanyRepository $companyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyRepository = $this->getContainer()->get(CompanyRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->companyRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider getQueriesByCompanySearch
     *
     * @param string[] $expectedCompanyNames
     */
    public function testFindByCompanyForAutocomplete(string $query, array $expectedCompanyNames): void
    {
        $companySearchData = new CompanySearchData();
        $companySearchData->companySearch = $query;

        $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadCompanyFromNovosibirskRegion::class,
            LoadCompanyWithCustomDescription::class,
            LoadTackleShopsCompany::class,
            LoadOldCompany::class,
        ]);

        $companies = $this->companyRepository->findForAutocomplete($companySearchData);

        $actualCompanyNames = array_map(function ($company) {
            return $company->getName();
        }, $companies);

        $this->assertSame($expectedCompanyNames, $actualCompanyNames);

        $this->assertCount(count($expectedCompanyNames), $companies);
    }

    /**
     * @return string[]
     */
    public function getQueriesByCompanySearch(): array
    {
        return [
            [
                'query' => 'рыб',
                'expectedCompanyNames' => [
                    'Рыбный Мир',
                    'Рыболов',
                    'Мототехника и катера для рыбалки',
                ],
            ],

            [
                'query' => 'рыбо',
                'expectedCompanyNames' => [
                    'Рыболов',
                ],
            ],

            [
                'query' => 'Краткое описание',
                'expectedCompanyNames' => [
                    'Компания, которую не обновляли более двух лет',
                    'Мототехника и катера для рыбалки',
                    'company-with-custom-description',
                ],
            ],

            [
                'query' => 'каст',
                'expectedCompanyNames' => [
                    'company-with-custom-description',
                ],
            ],
        ];
    }


    public function testFindForAutocompleteOneCompany(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithRegion::class,
        ])->getReferenceRepository();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyWithRegion::REFERENCE_NAME);
        assert($expectedCompany instanceof Company);

        $companySearchData = new CompanySearchData();
        $companySearchData->companySearch = 'region';

        // TODO https://resolventa.atlassian.net/browse/FS-3079
        // Временное решение, настроить для работы со всеми адресами из коллекции
        $companySearchData->regionId = $expectedCompany->getContact()->getLocations()->first()->getRegion()->getId();
        $expectedCompanyName = $expectedCompany->getName();

        $companies = $this->companyRepository->findForAutocomplete($companySearchData);

        $this->assertCount(1, $companies);
        $this->assertSame($companies[0]->getName(), $expectedCompanyName);
    }

    public function testFindForAutocompleteOneCompanyByDescription(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithCustomDescription::class,
        ])->getReferenceRepository();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyWithCustomDescription::REFERENCE_NAME);
        assert($expectedCompany instanceof Company);

        $companySearchData = new CompanySearchData();
        $companySearchData->companySearch = 'кастом';
        $expectedCompanyName = $expectedCompany->getName();

        $companies = $this->companyRepository->findForAutocomplete($companySearchData);

        $this->assertCount(1, $companies);
        $this->assertSame($companies[0]->getName(), $expectedCompanyName);
    }

    public function testFindForAutocompleteOneCompanyByScopeActivity(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithCustomDescription::class,
        ])->getReferenceRepository();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyWithCustomDescription::REFERENCE_NAME);
        assert($expectedCompany instanceof Company);

        $companySearchData = new CompanySearchData();
        $companySearchData->companySearch = 'описание';
        $expectedCompanyName = $expectedCompany->getName();

        $companies = $this->companyRepository->findForAutocomplete($companySearchData);

        $this->assertCount(1, $companies);
        $this->assertSame($companies[0]->getName(), $expectedCompanyName);
    }

    public function testFindForAutocompleteByRubric(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadAquaMotorcycleShopsRubric::class,
            LoadTackleShopsRubric::class,
        ])->getReferenceRepository();

        $expectedCompany = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($expectedCompany instanceof Company);

        $expectedRubric = $referenceRepository->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);
        assert($expectedRubric instanceof Rubric);

        $nonExpectedRubric = $referenceRepository->getReference(LoadTackleShopsRubric::REFERENCE_NAME);
        assert($nonExpectedRubric instanceof Rubric);

        $expectedCompanyName = $expectedCompany->getName();

        $companySearchDataWithExpectedRubric = new CompanySearchData();
        $companySearchDataWithExpectedRubric->companySearch = $expectedCompanyName;
        $companySearchDataWithExpectedRubric->rubric = $expectedRubric;

        $companySearchDataWithoutNonExpectedRubric = new CompanySearchData();
        $companySearchDataWithoutNonExpectedRubric->companySearch = $expectedCompanyName;
        $companySearchDataWithoutNonExpectedRubric->rubric = $nonExpectedRubric;

        $expectedCompanies = $this->companyRepository->findForAutocomplete($companySearchDataWithExpectedRubric);
        $nonExpectedCompanies = $this->companyRepository->findForAutocomplete($companySearchDataWithoutNonExpectedRubric);

        $this->assertContains($expectedCompany, $expectedCompanies);
        $this->assertNotContains($expectedCompany, $nonExpectedCompanies);
    }

    public function testOwnerCompaniesCanBeFound(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $owner = $company->getOwner();

        $companies = $this->companyRepository->getAllOwnedByAuthor($owner);

        $this->assertCount(1, $companies);
        $this->assertContains($company, $companies);
    }

    public function testNotOwnedByAnyCompaniesShouldNotBeFoundForAnAnonymousOwner(): void
    {
        $this->loadFixtures([
            LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::class,
        ])->getReferenceRepository();

        $anonymous = new AnonymousAuthor('anonymous');

        $companies = $this->companyRepository->getAllOwnedByAuthor($anonymous);

        $this->assertCount(0, $companies);
    }

    public function testSimilarCompaniesOrder(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestRegion::class,
            LoadSetOfSimilarCompanies::class,
        ])->getReferenceRepository();

        $originalCompany = $referenceRepository->getReference(
            LoadSetOfSimilarCompanies::ORIGINAL_COMPANY_REFERENCE
        );
        assert($originalCompany instanceof Company);

        $firstCompanyInList = $referenceRepository->getReference(
            LoadSetOfSimilarCompanies::COMPANY_WITH_SAME_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE
        );
        assert($firstCompanyInList instanceof Company);

        $secondCompanyInList = $referenceRepository->getReference(
            LoadSetOfSimilarCompanies::COMPANY_WITH_DIFFERENT_COORDINATES_AND_TWO_COMMON_RUBRICS_REFERENCE
        );
        assert($secondCompanyInList instanceof Company);

        $thirdCompanyInList = $referenceRepository->getReference(
            LoadSetOfSimilarCompanies::COMPANY_WITH_SAME_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE
        );
        assert($thirdCompanyInList instanceof Company);

        $fourthCompanyInList = $referenceRepository->getReference(
            LoadSetOfSimilarCompanies::COMPANY_WITH_DIFFERENT_COORDINATES_AND_ONE_COMMON_RUBRIC_REFERENCE
        );
        assert($fourthCompanyInList instanceof Company);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $similarCompanies = $this->companyRepository->findSimilarPublicCompaniesNearby($originalCompany, $this->createMock(Region::class));

        $this->assertContains($firstCompanyInList, $similarCompanies);
        $this->assertContains($secondCompanyInList, $similarCompanies);
        $this->assertContains($thirdCompanyInList, $similarCompanies);
        $this->assertContains($fourthCompanyInList, $similarCompanies);
    }

    public function testSimilarCompaniesShouldBeEmptyIfNoCommonRubrics(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestRegion::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadAquaMotorcycleShopsContact::class,
            LoadPaidReservoirsCompany::class,
            LoadPaidReservoirsContact::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $similarCompanies = $this->companyRepository->findSimilarPublicCompaniesNearby($company, $region);

        $this->assertCount(0, $similarCompanies);
    }

    public function testSimilarCompaniesNearbyShouldNotContainHiddenCompanies(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestRegion::class,
            LoadHiddenCompany::class,
            LoadSimilarCompanyNearbyHiddenCompany::class,
        ])->getReferenceRepository();

        $publicCompany = $referenceRepository->getReference(LoadSimilarCompanyNearbyHiddenCompany::REFERENCE_NAME);
        assert($publicCompany instanceof Company);
        $hiddenCompany = $referenceRepository->getReference(LoadHiddenCompany::REFERENCE_NAME);
        assert($hiddenCompany instanceof Company);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $similarCompaniesNearby = $this->companyRepository->findSimilarPublicCompaniesNearby($publicCompany, $region);

        $this->assertNotContains($hiddenCompany, $similarCompaniesNearby);
    }

    public function testCreateQueryBuilderForFindBySearchDataWithOnlyRegionId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithRegion::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $searchData = new CompanySearchData();
        $searchData->regionId = $region->getId();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyWithRegion::REFERENCE_NAME);

        $foundCompanies = $this->companyRepository->createQueryBuilderToFindPublicBySearchData($searchData)->getQuery()->getResult();

        $this->assertCount(1, $foundCompanies);
        $this->assertContains($expectedCompany, $foundCompanies);
    }

    public function testCreateQueryBuilderForFindBySearchDataWithRegionId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithRegion::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $searchData = new CompanySearchData();
        $searchData->regionId = $region->getId();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyWithRegion::REFERENCE_NAME);

        $foundCompanies = $this->companyRepository->createQueryBuilderToFindPublicBySearchData($searchData)->getQuery()->getResult();

        $this->assertCount(1, $foundCompanies);
        $this->assertContains($expectedCompany, $foundCompanies);
    }

    public function testFindCompaniesByRegionShouldBeCompaniesWithDeliveryToRegionsAvailable(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithIrkutskRegion::class,
            LoadCompanyFromNovosibirskRegionWithDeliveryToRegionsAvailable::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadIrkutskRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $searchData = new CompanySearchData();
        $searchData->regionId = $region->getId();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyFromNovosibirskRegionWithDeliveryToRegionsAvailable::REFERENCE_NAME);

        $foundCompanies = $this->companyRepository->createQueryBuilderToFindPublicBySearchData($searchData)->getQuery()->getResult();

        $this->assertCount(2, $foundCompanies);
        $this->assertContains($expectedCompany, $foundCompanies);
    }
}
