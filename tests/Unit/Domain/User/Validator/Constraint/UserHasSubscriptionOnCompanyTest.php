<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Subscription\UnsubscribeFromCompanyCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Validator\Constraint\UserHasSubscriptionOnCompany;
use App\Domain\User\Validator\Constraint\UserHasSubscriptionOnCompanyValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class UserHasSubscriptionOnCompanyTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private UserHasSubscriptionOnCompanyValidator $userIsSubscriptionOnCompanyValidator;
    private UserHasSubscriptionOnCompany $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->userIsSubscriptionOnCompanyValidator = new UserHasSubscriptionOnCompanyValidator();
        $this->userIsSubscriptionOnCompanyValidator->initialize($this->executionContext);

        $this->constraint = new UserHasSubscriptionOnCompany();
    }

    public function testUserHasNotSubscriptionOnCompany(): void
    {
        $company = $this->createMock(Company::class);
        $user = $this->createMock(User::class);

        $unsubscribeFromCompanyCommand = $this->getUnsubscribeFromCompanyCommandMock($user, $company);

        $this->userIsSubscriptionOnCompanyValidator->validate($unsubscribeFromCompanyCommand, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
        $this->assertEquals($this->executionContext->getViolationMessages()[0], 'Пользователь не подписан на компанию.');
    }

    public function testUserHasSubscriptionOnCompany(): void
    {
        $company = $this->createMock(Company::class);
        $user = $this->getUserWithSubscriptionOnCompanyMock();

        $unsubscribeFromCompanyCommand = $this->getUnsubscribeFromCompanyCommandMock($user, $company);

        $this->userIsSubscriptionOnCompanyValidator->validate($unsubscribeFromCompanyCommand, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    private function getUnsubscribeFromCompanyCommandMock(User $subscriber, Company $company): UnsubscribeFromCompanyCommand
    {
        $stub = $this->createMock(UnsubscribeFromCompanyCommand::class);
        $stub
            ->method('getSubscriber')
            ->willReturn($subscriber);
        $stub
            ->method('getCompany')
            ->willReturn($company);

        return $stub;
    }

    private function getUserWithSubscriptionOnCompanyMock(): User
    {
        $user = $this->createMock(User::class);

        $user
            ->method('isCompanySubscriber')
            ->willReturn(true);

        return $user;
    }
}
