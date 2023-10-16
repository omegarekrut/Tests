<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Auth\Visitor\CompanyAuthor\CompanyAuthorInSessionStorage;
use App\Domain\Company\Command\SaveCompanyAuthorForRecordsCommand;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\Functional\TestCase;

class SaveCompanyAuthorForRecordsHandlerTest extends TestCase
{
    public function testHandleWithCompany(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $expectedCompany = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);

        $command = new SaveCompanyAuthorForRecordsCommand();
        $command->companyId = $expectedCompany->getId();

        $this->getCommandBus()->handle($command);

        $this->assertSame($expectedCompany, $this->getCompanyAuthorFromSession());
    }

    private function getCompanyAuthorFromSession(): ?Company
    {
        $companyAuthorService = $this->getContainer()->get(CompanyAuthorInSessionStorage::class);

        return $companyAuthorService->getCompanyAuthorFromSession();
    }
}
