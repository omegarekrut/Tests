<?php

namespace Tests\Unit\Security\Voter\Company\Employee;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Security\Voter\Company\Employee\ManageEmployeeVoter;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class ManageEmployeeVoterTest extends TestCase
{
    private const ATTRIBUTE = 'MANAGE_EMPLOYEE';
    private ManageEmployeeVoter $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = new ManageEmployeeVoter();
    }

    public function testVoteAllowForCompanyOwner(): void
    {
        $ownerUser = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithOwner($ownerUser);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($ownerUser), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForCompanyEmployee(): void
    {
        $subject = $this->createMockCompanyWithEmployee();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
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
            $this->voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteWithIncorrectAttribute(): void
    {
        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, ['WRONG_ATTRIBUTE']),
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
