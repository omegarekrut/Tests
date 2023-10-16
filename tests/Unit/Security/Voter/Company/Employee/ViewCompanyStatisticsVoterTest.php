<?php

namespace Tests\Unit\Security\Voter\Company\Employee;

use App\Domain\BusinessSubscription\Entity\Tariff;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffRestrictions;
use App\Domain\BusinessSubscription\Repository\BusinessSubscriptionRepository;
use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Security\Voter\Company\Employee\ViewCompanyStatisticsVoter;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class ViewCompanyStatisticsVoterTest extends TestCase
{
    private const ATTRIBUTE = 'VIEW_COMPANY_STATISTICS';

    public function testVoteAllowForCompanyOwner(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $ownerUser = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithOwner($ownerUser);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->getUserTokenInstance($ownerUser), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForCompanyEmployee(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $subject = $this->createMockCompanyWithEmployee();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForAdminRole(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithRestrictedAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForEmployeeOfCompanyWithTariffRestrictingAccessToStatistics(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithRestrictedAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $subject = $this->createMockCompanyWithEmployeeAndWithoutSubscription();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForOwnerOfCompanyWithTariffRestrictingAccessToStatistics(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithRestrictedAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $ownerUser = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithOwnerAndWithoutSubscription($ownerUser);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForUser(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteWithIncorrectSubject(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $subject = new stdClass();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->getUserTokenInstance(null), $subject, [self::ATTRIBUTE])
        );
    }

    public function testVoteWithIncorrectAttribute(): void
    {
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToStatistics();
        $voter = new ViewCompanyStatisticsVoter($businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->getUserTokenInstance($this->createmockAdmin()), $subject, ['VIEW_COMPANY_STATISTICS_WRONG']),
        );
    }

    private function getUserTokenInstance(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        return $token;
    }

    private function createMockCompany(): Company
    {
        return $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
    }

    private function createMockCompanyWithOwner(User $owner): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('getOwner')->willReturn($owner);
        $company->method('isOwnedByUser')->willReturn(true);

        return $company;
    }

    private function createMockCompanyWithEmployee(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isEmployee')->willReturn(true);

        return $company;
    }

    private function createMockCompanyWithOwnerAndWithoutSubscription(User $owner): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('getOwner')->willReturn($owner);
        $company->method('isOwnedByUser')->willReturn(true);

        return $company;
    }

    private function createMockCompanyWithEmployeeAndWithoutSubscription(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isEmployee')->willReturn(true);

        return $company;
    }

    private function createMockUser(int $id): User
    {
        return $this->createConfiguredMock(User::class, [
            'getId' => $id,
        ]);
    }

    private function createMockAdmin(): User
    {
        return $this->createConfiguredMock(User::class, [
            'hasAdminRole' => true,
        ]);
    }

    private function createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToStatistics(): BusinessSubscriptionRepository
    {
        $restrictions = $this->getMockBuilder(TariffRestrictions::class)->disableOriginalConstructor()->getMock();
        $restrictions->method('isAccessToStatisticsRestricted')->willReturn(false);

        return $this->createMockBusinessSubscriptionRepositoryHavingTariffWithRestrictions($restrictions);
    }

    private function createMockBusinessSubscriptionRepositoryThatReturnsTariffWithRestrictedAccessToStatistics(): BusinessSubscriptionRepository
    {
        $restrictions = $this->getMockBuilder(TariffRestrictions::class)->disableOriginalConstructor()->getMock();
        $restrictions->method('isAccessToStatisticsRestricted')->willReturn(true);

        return $this->createMockBusinessSubscriptionRepositoryHavingTariffWithRestrictions($restrictions);
    }

    private function createMockBusinessSubscriptionRepositoryHavingTariffWithRestrictions(TariffRestrictions $restrictions): BusinessSubscriptionRepository
    {
        $tariff = $this->getMockBuilder(Tariff::class)->disableOriginalConstructor()->getMock();
        $tariff->method('getRestrictions')->willReturn($restrictions);

        $repository = $this->getMockBuilder(BusinessSubscriptionRepository::class)->disableOriginalConstructor()->getMock();
        $repository->method('getTariffOfCompany')->willReturn($tariff);

        return $repository;
    }
}
