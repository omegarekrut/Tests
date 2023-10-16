<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\User\Command\Subscription\SubscribeOnUserCommand;
use App\Domain\User\Entity\Subscription\UserSubscription;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group user-subscription
 */
class SubscribeOnUserHandlerTest extends TestCase
{
    private User $subscriber;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->subscriber = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->subscriber, $this->user);

        parent::tearDown();
    }

    public function testSubscribeOnUserHandle(): void
    {
        $command = new SubscribeOnUserCommand($this->subscriber, $this->user);

        $this->getCommandBus()->handle($command);

        $subscription = $this->subscriber->getSubscriptions()->first();

        $this->assertInstanceOf(UserSubscription::class, $subscription);
        assert($subscription instanceof UserSubscription);

        $this->assertTrue($this->user === $subscription->getUser());
    }
}
