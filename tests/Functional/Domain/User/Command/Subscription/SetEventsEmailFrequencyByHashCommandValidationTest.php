<?php

namespace Tests\Functional\Domain\User\Command\Subscription;

use App\Domain\User\Command\Subscription\SetEventsEmailFrequencyByHashCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group user-subscription
 */
class SetEventsEmailFrequencyByHashCommandValidationTest extends ValidationTestCase
{
    /** @var SetEventsEmailFrequencyByHashCommand */
    private $command;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->command = new SetEventsEmailFrequencyByHashCommand(
            $this->user,
            $this->getContainer()->get(SubscribeNewsletterHashGenerator::class)->generate($this->user->getId())
        );
    }

    protected function tearDown(): void
    {
        unset($this->command);
        unset($this->user);

        parent::tearDown();
    }

    public function testEmptyHash(): void
    {
        $command = new SetEventsEmailFrequencyByHashCommand(
            $this->user,
            ''
        );
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('hash', 'Значение не должно быть пустым.');
    }

    public function testInvalidHash(): void
    {
        $command = new SetEventsEmailFrequencyByHashCommand(
            $this->user,
            'Invalid'
        );
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('', 'Передан неверный хеш.');
    }

    public function testEmptyEmailFrequencyValue(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('emailFrequencyValue', 'Значение не выбрано.');
    }

    public function testInvalidEmailFrequencyValue(): void
    {
        $this->command->emailFrequencyValue = 'invalid-value';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('emailFrequencyValue', 'Невалидное значение.');
    }

    public function testCommandWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->emailFrequencyValue = 'never';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
