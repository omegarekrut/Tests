<?php

namespace Tests\Functional\Domain\User\Command\Forum;

use App\Domain\User\Command\Forum\ReadUserNotificationsOnForumCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class ReadUserNotificationsOnForumCommandValidationTest extends ValidationTestCase
{
    /** @var ReadUserNotificationsOnForumCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ReadUserNotificationsOnForumCommand();
    }

    protected function tearDown(): void
    {
        unset($this->comment);

        parent::tearDown();
    }

    public function testUserMustBeExistsById(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'userId',
            'User not found.'
        );
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->command->userId = $user->getId();
        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
