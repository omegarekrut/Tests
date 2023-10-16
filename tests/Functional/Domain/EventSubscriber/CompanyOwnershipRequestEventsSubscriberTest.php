<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Entity\Notification\NewOwnershipRequestNotification;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\Company\Event\OwnershipRequestCreatedEvent;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\OwnershipRequest\LoadOwnershipRequestToFutureApprove;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class CompanyOwnershipRequestEventsSubscriberTest extends TestCase
{
    private OwnershipRequest $ownershipRequest;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadOwnershipRequestToFutureApprove::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->ownershipRequest = $referenceRepository->getReference(LoadOwnershipRequestToFutureApprove::REFERENCE_NAME);
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset(
            $this->ownershipRequest,
            $this->user,
        );

        parent::tearDown();
    }

    public function testNotificationShouldBeSentAfterRequestCreation(): void
    {
        $notificationRepository = $this->getEntityManager()->getRepository(NewOwnershipRequestNotification::class);

        $numberOfNotifications = $notificationRepository->count([]);

        $this->getEventDispatcher()->dispatch(new OwnershipRequestCreatedEvent($this->ownershipRequest));

        $this->assertGreaterThan($numberOfNotifications, $notificationRepository->count([]));
    }
}
