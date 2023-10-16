<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\User\Command\Subscription\DoNotDisturbUserByEmailCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group user-subscription
 */
class DoNotDisturbUserByEmailHandlerTest extends TestCase
{
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testAfterHandlingUserShouldHaveDoNotDisturbStatus(): void
    {
        $this->assertTrue($this->user->canBeDisturbedByEmail());

        $command = new DoNotDisturbUserByEmailCommand();
        $command->email = $this->user->getEmailAddress();

        $this->getCommandBus()->handle($command);

        $this->assertFalse($this->user->canBeDisturbedByEmail());
    }
}
