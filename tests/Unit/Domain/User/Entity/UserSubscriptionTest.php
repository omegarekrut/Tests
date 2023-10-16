<?php

namespace Tests\Unit\Domain\User\Entity;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use App\Domain\User\Entity\Subscription\CompanySubscription;
use App\Domain\User\Entity\Subscription\UserSubscription;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Tests\Traits\UserGeneratorTrait;
use Tests\Unit\TestCase;
use LogicException;

class UserSubscriptionTest extends TestCase
{

    use UserGeneratorTrait;

    private User $user;
    private User $anotherUser;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->generateUser();
        $this->anotherUser = $this->createMockUser();
        $this->company = $this->createCompany();
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->anotherUser,
            $this->company,
        );

        parent::tearDown();
    }

    public function testSubscribeToUser(): void
    {
        $userSubscription = new UserSubscription($this->user, $this->anotherUser);

        $this->assertEquals($this->anotherUser, $userSubscription->getUser());
    }

    public function testSubscribeToYourself(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot follow himself.');

        new UserSubscription($this->user, $this->user);
    }

    public function testUnsubscribeFromUserWithSubscription(): void
    {
        $this->user->subscribeOnUser($this->anotherUser);

        $this->user->unsubscribeFromUser($this->anotherUser);

        $this->assertEmpty($this->user->getSubscriptions());
    }

    public function testUnsubscribeFromUserWithoutSubscription(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User is not subscribed to user.');

        $this->user->unsubscribeFromUser($this->anotherUser);
    }

    public function testSubscribeOnUserWhenSubscriptionAlreadyExists(): void
    {
        $this->user->subscribeOnUser($this->anotherUser);
        $this->user->subscribeOnUser($this->anotherUser);

        $this->assertCount(1, $this->user->getSubscriptions());
    }

    public function testSubscribeToCompany(): void
    {
        $this->company->setOwner($this->anotherUser);

        $companySubscription = new CompanySubscription($this->user, $this->company);

        $this->assertEquals($this->company, $companySubscription->getCompany());
    }

    public function testSubscribeToYourCompany(): void
    {
        $this->company->setOwner($this->user);

        $companySubscription = new CompanySubscription($this->user, $this->company);

        $this->assertEquals($this->company, $companySubscription->getCompany());
    }

    public function testUnsubscribeFromCompanyWithSubscription(): void
    {
        $this->user->subscribeOnCompany($this->company);

        $this->user->unsubscribeFromCompany($this->company);

        $this->assertFalse($this->user->isCompanySubscriber($this->company->getId()));
        $this->assertEmpty($this->user->getSubscriptions());
    }

    public function testUnsubscribeFromCompanyWithoutSubscription(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User is not subscribed to the company.');

        $this->user->unsubscribeFromCompany($this->company);
    }

    public function testSubscribeOnCompanyWhenSubscriptionAlreadyExists(): void
    {
        $this->user->subscribeOnCompany($this->company);
        $this->user->subscribeOnCompany($this->company);

        $this->assertCount(1, $this->user->getSubscriptions());
    }

    public function testSubscribeToHiddenCompany(): void
    {
        $this->company->setOwner($this->anotherUser);
        $this->company->hide();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot subscribe to hidden company');

        new CompanySubscription($this->user, $this->company);
    }

    public function testUnsubscribeFromHiddenCompany(): void
    {
        $this->user->subscribeOnCompany($this->company);
        $this->company->hide();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User cannot unsubscribe from hidden company');

        $this->user->unsubscribeFromCompany($this->company);
    }

    private function createCompany(): Company
    {
        return new Company(
            Uuid::uuid4(),
            'Company name',
            'Company slug',
            'Company shortUuid',
            'Company scopeActivity',
            new ArrayCollection([$this->createMockRubric()])
        );
    }

    private function createMockUser(): User
    {
        $mockUser = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $mockUser->method('getId')->willReturn(time());

        return $mockUser;
    }

    private function createMockRubric(): Rubric
    {
        return $this->getMockBuilder(Rubric::class)->disableOriginalConstructor()->getMock();
    }
}
