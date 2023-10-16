<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\HideCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

/**
 * @group company
 */
class HideCompanyHandlerTest extends TestCase
{
    private CompanyRepository $companyRepository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
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

    public function testAfterCompanyHidden(): void
    {
        $hideCompanyCommand = new HideCompanyCommand($this->company);
        $this->getCommandBus()->handle($hideCompanyCommand);

        $this->getEntityManager()->clear();

        $actualCompany = $this->companyRepository->findById($this->company->getId());

        $this->assertFalse($actualCompany->isPublic());
    }
}
