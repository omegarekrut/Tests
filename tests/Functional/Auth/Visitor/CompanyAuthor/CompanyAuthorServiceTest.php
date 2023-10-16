<?php

namespace Tests\Functional\Auth\Visitor\CompanyAuthor;

use App\Auth\Visitor\CompanyAuthor\CompanyAuthorInSessionStorage;
use App\Auth\Visitor\CompanyAuthor\CompanyAuthorService;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\Functional\TestCase;

class CompanyAuthorServiceTest extends TestCase
{
    private Company $company;
    private CompanyAuthorService $companyAuthorService;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);

        $companyAuthorInSessionStorage = $this->getContainer()->get(CompanyAuthorInSessionStorage::class);
        assert($companyAuthorInSessionStorage instanceof CompanyAuthorInSessionStorage);

        $this->companyAuthorService = new CompanyAuthorService($companyAuthorInSessionStorage);
    }

    public function testGetCompanyAuthorFromAuthorService(): void
    {
        $this->companyAuthorService->setCompanyAuthor($this->company);

        $this->assertEquals($this->company, $this->companyAuthorService->getCompanyAuthor());
    }

    public function testClearCompanyAuthorFromAuthorService(): void
    {
        $this->companyAuthorService->setCompanyAuthor($this->company);

        $this->companyAuthorService->clearCompanyAuthor();

        $this->assertNull($this->companyAuthorService->getCompanyAuthor());
    }

    public function testGetCompanyAuthorFromAuthorServiceWithEmptySession(): void
    {
        $this->assertNull($this->companyAuthorService->getCompanyAuthor());
    }
}
