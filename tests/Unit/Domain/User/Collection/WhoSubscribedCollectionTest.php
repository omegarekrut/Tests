<?php

namespace Tests\Unit\Domain\User\Collection;

use App\Domain\User\Collection\WhoSubscribedCollection;
use App\Domain\User\Entity\Subscription\Subscription;
use App\Domain\User\Entity\User;
use Tests\Unit\TestCase;

final class WhoSubscribedCollectionTest extends TestCase
{
    public function testSubscribersCanBeReceived(): void
    {
        $subscriptions = new WhoSubscribedCollection([$this->createSubscription()]);

        $subscribers = $subscriptions->getSubscribers();

        $this->assertCount(1, $subscribers);
        $this->assertInstanceOf(User::class, $subscribers->first());
    }

    private function createSubscription(): Subscription
    {
        return $this->createConfiguredMock(Subscription::class, [
            'getSubscriber' => $this->createMock(User::class),
        ]);
    }
}
