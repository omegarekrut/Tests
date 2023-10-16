<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\RestoreCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use Tests\DataFixtures\ORM\Company\Company\LoadHiddenCompany;
use Tests\Functional\TestCase;

/** @group company */
class RestoreCompanyHandlerTest extends TestCase
{
    private CompanyRepository $companyRepository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadHiddenCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadHiddenCompany::REFERENCE_NAME);
        $this->companyRepository = $this->getContainer()->get(CompanyRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->company,
            $this->companyRepository
        );

        parent::tearDown();
    }

    public function testAfterCompanyRestored(): void
    {
        $restoreCompanyCommand = new RestoreCompanyCommand($this->company);
        $this->getCommandBus()->handle($restoreCompanyCommand);

        $this->getEntityManager()->clear();

        $actualCompany = $this->companyRepository->findById($this->company->getId());

        $this->assertTrue($actualCompany->isPublic());
    }
}
