<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\UpdateRegionOfCompanyViaLocationCommand;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithFixedCoordinates;
use Tests\Functional\ValidationTestCase;

class UpdateRegionOfCompanyViaLocationCommandValidationTest extends ValidationTestCase
{
    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithFixedCoordinates::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithFixedCoordinates::REFERENCE_NAME);

        $command = new UpdateRegionOfCompanyViaLocationCommand($company->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testCommandFilledWithNonExistentCompanyShouldCauseErrors(): void
    {
        $command = new UpdateRegionOfCompanyViaLocationCommand(Uuid::uuid4());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyId', 'Компания не найдена.');
    }
}
