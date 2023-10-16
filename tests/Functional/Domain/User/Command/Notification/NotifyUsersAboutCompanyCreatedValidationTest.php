<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifyUsersAboutCompanyCreatedCommand;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\Functional\ValidationTestCase;

class NotifyUsersAboutCompanyCreatedValidationTest extends ValidationTestCase
{
    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        $command = new NotifyUsersAboutCompanyCreatedCommand($company->getId());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testInvalidCompanyId(): void
    {
        $command = new NotifyUsersAboutCompanyCreatedCommand(Uuid::uuid4());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyId', 'Компания не найдена.');
    }
}
