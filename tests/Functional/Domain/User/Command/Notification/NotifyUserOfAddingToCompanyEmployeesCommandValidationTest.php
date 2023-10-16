<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Notification\NotifyUserOfAddingToCompanyEmployeesCommand;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifyUserOfAddingToCompanyEmployeesCommandValidationTest extends ValidationTestCase
{
    private Company $company;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
    }

    public function testCommandValidationFailedWithIncorrectUserId(): void
    {
        $companyId = $this->company->getId();
        $incorrectUserId = 0;
        $invalidCommand = new NotifyUserOfAddingToCompanyEmployeesCommand($incorrectUserId, $companyId);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('userId', 'Пользователь не найден.');
    }

    public function testCommandValidationPassedWithCorrectUserIdAndCompanyId(): void
    {
        $correctCompanyId = $this->company->getId();
        $correctUserId = $this->user->getId();
        $validCommand = new NotifyUserOfAddingToCompanyEmployeesCommand($correctUserId, $correctCompanyId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    /**
     * @throws \Exception
     */
    public function testCommandValidationFailedWithIncorrectCompanyId(): void
    {
        $userId = $this->user->getId();
        $incorrectCompanyId = Uuid::uuid4();
        $invalidCommand = new NotifyUserOfAddingToCompanyEmployeesCommand($userId, $incorrectCompanyId);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('companyId', 'Такой компании не существует.');
    }
}
