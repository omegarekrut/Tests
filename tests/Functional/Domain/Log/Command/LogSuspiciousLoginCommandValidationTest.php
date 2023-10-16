<?php

namespace Tests\Functional\Domain\Log\Command;

use App\Domain\Log\Command\LogSuspiciousLoginCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;
use Tests\Functional\ValidationTestCase;

/**
 * @group log
 */
class LogSuspiciousLoginCommandValidationTest extends ValidationTestCase
{
    /** @var LogSuspiciousLoginCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new LogSuspiciousLoginCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testAuthorizedUsersMustBeFilled(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('currentAuthorizedUser', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('previousAuthorizedUser', 'Значение не должно быть пустым.');
    }

    public function testAuthorizedUsersMustBeRealUserObject(): void
    {
        $this->command->currentAuthorizedUser = 'not user';
        $this->command->previousAuthorizedUser = 'not user';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('currentAuthorizedUser', sprintf('Тип значения должен быть %s.', User::class));
        $this->assertFieldInvalid('previousAuthorizedUser', sprintf('Тип значения должен быть %s.', User::class));
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadUserWithDotInUsername::class,
        ])->getReferenceRepository();

        /** @var User $previousAuthorizedUser */
        $previousAuthorizedUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var User $currentAuthorizedUser */
        $currentAuthorizedUser = $referenceRepository->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);

        $this->command->previousAuthorizedUser = $previousAuthorizedUser;
        $this->command->currentAuthorizedUser = $currentAuthorizedUser;

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
