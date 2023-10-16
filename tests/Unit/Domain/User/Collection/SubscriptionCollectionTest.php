<?php

namespace Tests\Unit\Domain\User\Collection;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Collection\SubscriptionCollection;
use App\Domain\User\Entity\Subscription\CompanySubscription;
use App\Domain\User\Entity\Subscription\Subscription;
use App\Domain\User\Entity\Subscription\UserSubscription;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

class SubscriptionCollectionTest extends TestCase
{
    public function testCompaniesSubscriptionsCanBeReceived(): void
    {
        $expectedCompanySubscription = $this->createCompanySubscription($this->createMock(Company::class));
        $subscriptions = new SubscriptionCollection([
            $this->createSubscription(),
            $expectedCompanySubscription,
        ]);

        $companiesSubscriptions = $subscriptions->getCompaniesSubscriptions();

        $this->assertCount(1, $companiesSubscriptions);
        $this->assertEquals($expectedCompanySubscription, $companiesSubscriptions->first());
    }

    public function testUsersSubscriptionsCanBeReceived(): void
    {
        $expectedUserSubscription = $this->createUserSubscription($this->createMock(User::class));
        $subscriptions = new SubscriptionCollection([
            $this->createSubscription(),
            $expectedUserSubscription,
        ]);

        $usersSubscriptions = $subscriptions->getUsersSubscriptions();

        $this->assertCount(1, $usersSubscriptions);
        $this->assertEquals($expectedUserSubscription, $usersSubscriptions->first());
    }

    public function testCompaniesFromUserSubscriptionsCanBeReceived(): void
    {
        $companyMock = $this->createMock(Company::class);
        $companyMock
            ->method('isPublic')
            ->willReturn(true);

        $expectedCompanySubscription = $this->createCompanySubscription($companyMock);

        $subscriptions = new SubscriptionCollection([
            $this->createSubscription(),
            $expectedCompanySubscription,
        ]);

        $companiesFromUserSubscriptions = $subscriptions->getCompaniesFromUserSubscriptions();

        $this->assertCount(1, $companiesFromUserSubscriptions);
        $this->assertInstanceOf(Company::class, $companiesFromUserSubscriptions->first());
    }

    public function testIsCompanySubscriber(): void
    {
        $company = $this->createConfiguredMock(Company::class, [
            'getId' => Uuid::uuid4(),
        ]);
        assert($company instanceof Company);

        $subscriptions = new SubscriptionCollection([
            $this->createSubscription(),
            $this->createCompanySubscription($company),
        ]);

        $this->assertTrue($subscriptions->existsSubscriptionOnCompany($company->getId()));
    }

    public function testIsUserSubscriber(): void
    {
        $user = $this->createConfiguredMock(User::class, [
            'getId' => 1,
        ]);
        assert($user instanceof User);

        $subscriptions = new SubscriptionCollection([
            $this->createSubscription(),
            $this->createUserSubscription($user),
        ]);

        $this->assertTrue($subscriptions->existsSubscriptionOnUser($user->getId()));
    }

    private function createSubscription(): Subscription
    {
        return $this->createConfiguredMock(Subscription::class, [
            'getSubscriber' => $this->createMock(User::class),
        ]);
    }

    private function createCompanySubscription(Company $company): Subscription
    {
        return $this->createConfiguredMock(CompanySubscription::class, [
            'getSubscriber' => $this->createMock(User::class),
            'getCompany' => $company,
        ]);
    }

    private function createUserSubscription(User $user): Subscription
    {
        return $this->createConfiguredMock(UserSubscription::class, [
            'getSubscriber' => $this->createMock(User::class),
            'getUser' => $user,
        ]);
    }
}
