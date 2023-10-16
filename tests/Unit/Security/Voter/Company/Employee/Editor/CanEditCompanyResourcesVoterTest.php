<?php

namespace Tests\Unit\Security\Voter\Company\Employee\Editor;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Security\Voter\Company\Employee\Editor\CanEditCompanyResourcesVoter;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class CanEditCompanyResourcesVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_EDIT_COMPANY_RESOURCES';
    private CanEditCompanyResourcesVoter $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = new CanEditCompanyResourcesVoter();
    }

    public function testVoteAllowForCompanyOwner(): void
    {
        $ownerUser = $this->createMockUser(1);
        $subject = $this->createCompanyMockWithOwner($ownerUser);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($ownerUser), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForCompanyEmployee(): void
    {
        $employeeUser = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithEmployee();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($employeeUser), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForAdminRole(): void
    {
        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForUser(): void
    {
        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteWithIncorrectSubject(): void
    {
        $subject = new stdClass();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->getUserTokenInstance(null), $subject, [self::ATTRIBUTE])
        );
    }

    public function testVoteWithIncorrectAttribute(): void
    {
        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, ['CAN_EDIT_COMPANY_RESOURCES_WRONG']),
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

    private function createCompanyMockWithOwner(User $owner): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('getOwner')->willReturn($owner);
        $company->method('isOwnedByUser')->willReturn(true);

        return $company;
    }

    private function createMockCompanyWithEmployee(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
        $company->method('isUserCompanyEditor')->willReturn(true);

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
}
