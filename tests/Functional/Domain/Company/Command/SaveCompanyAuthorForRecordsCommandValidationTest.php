<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\SaveCompanyAuthorForRecordsCommand;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\Functional\ValidationTestCase;

class SaveCompanyAuthorForRecordsCommandValidationTest extends ValidationTestCase
{
    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);

        $command = new SaveCompanyAuthorForRecordsCommand();
        $command->companyId = $company->getId();

        $errors = $this->getValidator()->validate($command);

        $this->assertEmpty($errors);
    }

    public function testInvalidCompanyId(): void
    {
        $command = new SaveCompanyAuthorForRecordsCommand();
        $command->companyId = Uuid::uuid4();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyId', 'Компания не найдена.');
    }
}
