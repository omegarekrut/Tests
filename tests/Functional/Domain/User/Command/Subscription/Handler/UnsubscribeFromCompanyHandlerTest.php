<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Subscription\UnsubscribeFromCompanyCommand;
use App\Domain\User\Entity\Subscription\CompanySubscription;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithSubscriber;
use Tests\DataFixtures\ORM\User\LoadUserWithSubscriptionOnCompany;
use Tests\Functional\TestCase;

/**
 * @group user-subscription
 */
class UnsubscribeFromCompanyHandlerTest extends TestCase
{
    private User $subscriber;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithSubscriptionOnCompany::class,
            LoadCompanyWithSubscriber::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithSubscriber::REFERENCE_NAME);
        $this->subscriber = $referenceRepository->getReference(LoadUserWithSubscriptionOnCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->subscriber, $this->company);

        parent::tearDown();
    }

    public function testUnsubscribeSubscriberFromCompanyHandle(): void
    {
        $subscription = $this->subscriber->getSubscriptions()->first();
        $this->assertInstanceOf(CompanySubscription::class, $subscription);

        $unsubscribeCommand = new UnsubscribeFromCompanyCommand($this->subscriber, $this->company);
        $this->getCommandBus()->handle($unsubscribeCommand);

        $subscription = $this->subscriber->getSubscriptions()->first();

        $this->assertNotInstanceOf(CompanySubscription::class, $subscription);
        $this->assertFalse($subscription);
    }
}
