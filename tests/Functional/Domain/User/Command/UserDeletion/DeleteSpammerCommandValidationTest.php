<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion;

use App\Domain\User\Command\Deleting\DeleteSpammerCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadSpammerUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group user
 */
class DeleteSpammerCommandValidationTest extends ValidationTestCase
{
    /** @var DeleteSpammerCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new DeleteSpammerCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testCauseRequired(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('cause', 'Значение не должно быть пустым.');
    }

    public function testCauseShouldBeLessThan255(): void
    {
        $this->command->cause = str_repeat('c', 256);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('cause', 'Значение слишком длинное. Должно быть равно 255 символам или меньше.');
    }

    public function testSpammerMustBeExistsById(): void
    {
        $invalidSpammerId = -1;
        $this->command->spammerId = $invalidSpammerId;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('spammerId', 'Спамер не найден.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSpammerUser::class,
        ])->getReferenceRepository();


        /** @var User $spammer */
        $spammer = $referenceRepository->getReference(LoadSpammerUser::REFERENCE_NAME);

        $this->command->spammerId = $spammer->getId();
        $this->command->cause = 'some cause';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
