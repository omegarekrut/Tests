<?php

namespace Tests\Functional\Domain\Company\Command\Employee;

use App\Domain\Company\Command\Employee\AddEmployeeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Employee;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\Employee\LoadEmployeeEditor;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
final class AddEmployeeCommandValidationTest extends ValidationTestCase
{
    private AddEmployeeCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new AddEmployeeCommand();
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
        );

        parent::tearDown();
    }

    public function testValidationFailForNotExistedCompany(): void
    {
        $notExistingCompanyId = Uuid::uuid4();
        $this->command->companyId = $notExistingCompanyId;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('companyId', 'Компания не найдена.');
    }

    public function testValidationFailForNotExistedUser(): void
    {
        $this->command->userLoginOrEmail = 'not-existing-user@example.com';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userLoginOrEmail', 'Пользователь not-existing-user@example.com на нашем сайте не найден.');
    }

    public function testValidationFailForUserWhichIsOwnerOfCompany(): void
    {
        $company = $this->loadFixtures([LoadCompanyWithOwner::class])
            ->getReferenceRepository()
            ->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $this->command->companyId = $company->getId();
        $this->command->userLoginOrEmail = $company->getOwner()->getUserName();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userLoginOrEmail', 'Пользователь является владельцем компании.');
    }

    public function testValidationFailForUserWhichIsAlreadyCompanyEmployee(): void
    {
        $employee = $this->loadFixtures([LoadEmployeeEditor::class])
            ->getReferenceRepository()
            ->getReference(LoadEmployeeEditor::REFERENCE_NAME);
        assert($employee instanceof Employee);

        $this->command->companyId = $employee->getCompany()->getId();
        $this->command->userLoginOrEmail = $employee->getUser()->getLogin();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('userLoginOrEmail', 'Пользователь уже является сотрудником компании.');
    }

    public function testValidationPassedForCorrectCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);
        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($user instanceof User);

        $this->command->companyId = $company->getId();
        $this->command->userLoginOrEmail = $user->getLogin();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
