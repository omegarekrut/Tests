<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Subscription\SubscribeOnCompanyCommand;
use App\Domain\User\Entity\Subscription\CompanySubscription;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group user-subscription
 */
class SubscribeOnCompanyHandlerTest extends TestCase
{
    private User $userSubscriber;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $this->userSubscriber = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->userSubscriber, $this->company);

        parent::tearDown();
    }

    public function testSubscribeUserOnCompanyHandle(): void
    {
        $command = new SubscribeOnCompanyCommand($this->userSubscriber, $this->company);

        $this->getCommandBus()->handle($command);

        $subscription = $this->userSubscriber->getSubscriptions()->first();

        $this->assertInstanceOf(CompanySubscription::class, $subscription);
        assert($subscription instanceof CompanySubscription);

        $this->assertTrue($this->company === $subscription->getCompany());
    }

    public function testSubscribeCompanyOwnerOnCompanyHandle(): void
    {
        $companyOwner = $this->company->getOwner();
        assert($companyOwner instanceof User);

        $command = new SubscribeOnCompanyCommand($companyOwner, $this->company);

        $this->getCommandBus()->handle($command);

        $subscription = $companyOwner->getSubscriptions()->first();

        $this->assertInstanceOf(CompanySubscription::class, $subscription);
        assert($subscription instanceof CompanySubscription);

        $this->assertTrue($this->company === $subscription->getCompany());
    }
}
