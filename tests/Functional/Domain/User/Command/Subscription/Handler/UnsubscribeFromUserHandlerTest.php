<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\User\Command\Subscription\UnsubscribeFromUserCommand;
use App\Domain\User\Entity\User;
use LogicException;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithSubscriptionOnUser;
use Tests\Functional\TestCase;

/**
 * @group user-subscription
 */
class UnsubscribeFromUserHandlerTest extends TestCase
{
    private User $subscriber;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithSubscriptionOnUser::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->subscriber = $referenceRepository->getReference(LoadUserWithSubscriptionOnUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->subscriber, $this->user);

        parent::tearDown();
    }

    public function testUnsubscribeFromUserHandle(): void
    {
        $unsubscribeCommand = new UnsubscribeFromUserCommand($this->subscriber, $this->user);

        $this->getCommandBus()->handle($unsubscribeCommand);

        $this->assertFalse($this->subscriber->isUserSubscriber($this->user->getId()));
    }

    public function testUnsubscribeFromUserHandleWithUserWithoutSubscription(): void
    {
        $unsubscribeCommand = new UnsubscribeFromUserCommand($this->subscriber, $this->user);
        $this->getCommandBus()->handle($unsubscribeCommand);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User is not subscribed to user');

        $this->getCommandBus()->handle($unsubscribeCommand);
    }
}
