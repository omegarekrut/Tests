<?php

namespace Tests\Functional\Domain\Company\Command\Employee\Handler;

use App\Domain\Company\Command\Employee\DeleteEmployeeCommand;
use App\Domain\Company\Entity\Employee;
use Tests\DataFixtures\ORM\Company\Employee\LoadEmployeeEditor;
use Tests\Functional\TestCase;

/**
 * @group company
 */
final class DeleteEmployeeHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadEmployeeEditor::class,
        ])->getReferenceRepository();

        $employee = $referenceRepository->getReference(LoadEmployeeEditor::REFERENCE_NAME);
        assert($employee instanceof Employee);
        $company = $employee->getCompany();

        $command = new DeleteEmployeeCommand();
        $command->employeeId = $employee->getId();

        $this->getCommandBus()->handle($command);

        $this->assertEmpty($company->getEmployees());
    }
}
