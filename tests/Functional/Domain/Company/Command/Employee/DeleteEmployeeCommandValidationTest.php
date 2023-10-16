<?php

namespace Tests\Functional\Domain\Company\Command\Employee;

use App\Domain\Company\Command\Employee\DeleteEmployeeCommand;
use App\Domain\Company\Entity\Employee;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Employee\LoadEmployeeEditor;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
final class DeleteEmployeeCommandValidationTest extends ValidationTestCase
{
    private DeleteEmployeeCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new DeleteEmployeeCommand();
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
        );

        parent::tearDown();
    }

    public function testValidationFailForNotExistedEmployee(): void
    {
        $notExistingEmployeeId = Uuid::uuid4();
        $this->command->employeeId = $notExistingEmployeeId;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'employeeId',
            'Сотрудник не найден.',
        );
    }

    public function testValidationPassedForCorrectCommand(): void
    {
        $employee = $this->loadFixtures([LoadEmployeeEditor::class])
            ->getReferenceRepository()
            ->getReference(LoadEmployeeEditor::REFERENCE_NAME);
        assert($employee instanceof Employee);

        $this->command->employeeId = $employee->getId();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
