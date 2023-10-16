<?php

namespace Tests\Unit\Security\Voter\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Security\Voter\Company\CanBuySubscriptionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class CanBuySubscriptionVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_BUY_SUBSCRIPTION';

    private CanBuySubscriptionVoter $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = new CanBuySubscriptionVoter();
    }

    public function testVoteAllowForCompanyWithoutSubscriptionOwner(): void
    {
        $owner = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithoutSubscriptionWithOwner($owner);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($owner), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForCompanyWithSubscriptionOwner(): void
    {
        $owner = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithSubscriptionWithOwner($owner);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($owner), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForCompanyWithoutSubscriptionEmployee(): void
    {
        $employee = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithoutSubscriptionWithEmployee();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($employee), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForCompanyWithSubscriptionEmployee(): void
    {
        $employee = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithSubscriptionWithEmployee();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($employee), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForCompanyWithoutSubscrpiptionOutsideUser(): void
    {
        $user = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithoutSubscriptionWithOutsideUser();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($user), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForCompanyWithSubscrpiptionOutsideUser(): void
    {
        $user = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithSubscriptionWithOutsideUser();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($user), $subject, [self::ATTRIBUTE]),
        );
    }

    private function getUserTokenInstance(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        return $token;
    }

    private function createMockUser(int $id): User
    {
        return $this->createConfiguredMock(User::class, [
            'getId' => $id,
        ]);
    }

    private function createMockCompanyWithoutSubscriptionWithOwner(User $owner): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('getOwner')->willReturn($owner);
        $company->method('isOwnedByUser')->willReturn(true);
        $company->method('hasActiveSubscription')->willReturn(false);

        return $company;
    }

    private function createMockCompanyWithSubscriptionWithOwner(User $owner): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('getOwner')->willReturn($owner);
        $company->method('isOwnedByUser')->willReturn(true);
        $company->method('hasActiveSubscription')->willReturn(true);

        return $company;
    }

    private function createMockCompanyWithoutSubscriptionWithEmployee(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isEmployee')->willReturn(true);
        $company->method('hasActiveSubscription')->willReturn(false);

        return $company;
    }

    private function createMockCompanyWithSubscriptionWithEmployee(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isEmployee')->willReturn(true);
        $company->method('hasActiveSubscription')->willReturn(true);

        return $company;
    }

    private function createMockCompanyWithoutSubscriptionWithOutsideUser(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isOwnedByUser')->willReturn(false);
        $company->method('isEmployee')->willReturn(false);
        $company->method('hasActiveSubscription')->willReturn(false);

        return $company;
    }

    private function createMockCompanyWithSubscriptionWithOutsideUser(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isOwnedByUser')->willReturn(false);
        $company->method('isEmployee')->willReturn(false);
        $company->method('hasActiveSubscription')->willReturn(true);

        return $company;
    }
}
