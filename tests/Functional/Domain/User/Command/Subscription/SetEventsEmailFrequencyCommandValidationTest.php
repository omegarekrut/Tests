<?php

namespace Tests\Functional\Domain\User\Command\Subscription;

use App\Domain\User\Command\Subscription\SetEventsEmailFrequencyCommand;
use App\Domain\User\Entity\ValueObject\EmailFrequency;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group user-subscription
 */
class SetEventsEmailFrequencyCommandValidationTest extends ValidationTestCase
{
    /** @var SetEventsEmailFrequencyCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->command = new SetEventsEmailFrequencyCommand($referenceRepository->getReference(LoadTestUser::USER_TEST));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testEmptyValue(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('emailFrequencyValue', 'Значение не выбрано.');
    }

    public function testInvalidValue(): void
    {
        $this->command->emailFrequencyValue = 'invalid-value';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('emailFrequencyValue', 'Невалидное значение.');
    }

    public function testCommandWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->emailFrequencyValue = (string) EmailFrequency::never();

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
