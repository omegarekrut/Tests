<?php

namespace Tests\Unit\Security\Voter\Company\Employee\Editor;

use App\Domain\BusinessSubscription\Entity\Tariff;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffRestrictions;
use App\Domain\BusinessSubscription\Repository\BusinessSubscriptionRepository;
use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Security\Voter\Company\Employee\Editor\CanUploadCompanyBackgroundVoter;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CanUploadCompanyBackgroundVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_UPLOAD_COMPANY_BACKGROUND';

    public function testVoteAllowForCompanyWithTariffAllowingBackgroundUpload(): void
    {
        $authorizationChecker = $this->createMockAuthorizationCheckerWithAccessToCompanyResourcesEditing();
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToBackgroundUpload();

        $voter = new CanUploadCompanyBackgroundVoter($authorizationChecker, $businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForCompanyWithTariffRestrictingBackgroundUpload(): void
    {
        $authorizationChecker = $this->createMockAuthorizationCheckerWithAccessToCompanyResourcesEditing();
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithRestrictedBackgroundUpload();

        $voter = new CanUploadCompanyBackgroundVoter($authorizationChecker, $businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForUserWithDeniedAccess(): void
    {
        $authorizationChecker = $this->createMockAuthorizationCheckerWithoutAccessToCompanyResourcesEditing();
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToBackgroundUpload();

        $voter = new CanUploadCompanyBackgroundVoter($authorizationChecker, $businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteWithIncorrectSubject(): void
    {
        $authorizationChecker = $this->createMockAuthorizationCheckerWithAccessToCompanyResourcesEditing();
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToBackgroundUpload();

        $voter = new CanUploadCompanyBackgroundVoter($authorizationChecker, $businessSubscriptionRepository);

        $subject = new stdClass();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->getUserTokenInstance(null), $subject, [self::ATTRIBUTE])
        );
    }

    public function testVoteWithIncorrectAttribute(): void
    {
        $authorizationChecker = $this->createMockAuthorizationCheckerWithAccessToCompanyResourcesEditing();
        $businessSubscriptionRepository = $this->createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToBackgroundUpload();

        $voter = new CanUploadCompanyBackgroundVoter($authorizationChecker, $businessSubscriptionRepository);

        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, ['CAN_UPLOAD_BACKGROUND_WRONG']),
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

    private function createMockUser(int $id): User
    {
        return $this->createConfiguredMock(User::class, [
            'getId' => $id,
        ]);
    }

    private function createMockAuthorizationCheckerWithAccessToCompanyResourcesEditing(): AuthorizationCheckerInterface
    {
        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $authorizationChecker->method('isGranted')->willReturn(true);

        return $authorizationChecker;
    }

    private function createMockAuthorizationCheckerWithoutAccessToCompanyResourcesEditing(): AuthorizationCheckerInterface
    {
        $authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $authorizationChecker->method('isGranted')->willReturn(false);

        return $authorizationChecker;
    }

    private function createMockBusinessSubscriptionRepositoryThatReturnsTariffWithAccessToBackgroundUpload(): BusinessSubscriptionRepository
    {
        $restrictions = $this->getMockBuilder(TariffRestrictions::class)->disableOriginalConstructor()->getMock();
        $restrictions->method('isBackgroundUploadRestricted')->willReturn(false);

        return $this->createMockBusinessSubscriptionRepositoryHavingTariffWithRestrictions($restrictions);
    }

    private function createMockBusinessSubscriptionRepositoryThatReturnsTariffWithRestrictedBackgroundUpload(): BusinessSubscriptionRepository
    {
        $restrictions = $this->getMockBuilder(TariffRestrictions::class)->disableOriginalConstructor()->getMock();
        $restrictions->method('isBackgroundUploadRestricted')->willReturn(true);

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
