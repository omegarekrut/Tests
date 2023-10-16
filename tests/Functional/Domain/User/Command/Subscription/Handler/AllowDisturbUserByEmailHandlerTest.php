<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\User\Command\Subscription\AllowDisturbUserByEmailCommand;
use App\Domain\User\Entity\User;
use App\Module\EmailUnsubscription\UnsubscriptionManagerInterface;
use App\Module\EmailUnsubscription\UnsubscriptionManagerMock;
use Tests\DataFixtures\ORM\User\LoadUserWithDoNotDisturbByEmail;
use Tests\Functional\TestCase;

/**
 * @group user-subscription
 */
class AllowDisturbUserByEmailHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var UnsubscriptionManagerMock */
    private $unsubscriptionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithDoNotDisturbByEmail::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithDoNotDisturbByEmail::NAME);
        $this->unsubscriptionManager = $this->getContainer()->get(UnsubscriptionManagerInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->unsubscriptionManager
        );

        parent::tearDown();
    }

    public function testAfterHandlingUserShouldBeUnlocked(): void
    {
        $this->assertFalse($this->user->canBeDisturbedByEmail());

        $command = new AllowDisturbUserByEmailCommand($this->user);

        $this->getCommandBus()->handle($command);

        $this->assertTrue($this->user->canBeDisturbedByEmail());
        $this->assertEquals($this->user->getEmailAddress(), $this->unsubscriptionManager->getEmailWithResetUnsubscription());
    }
}
