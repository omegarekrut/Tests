<?php

namespace Tests\Unit\Security\Voter\Company\Employee;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Security\Voter\Company\Employee\ComplaintOnPublishedCompanyMaterialVoter;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class ComplaintOnPublishedCompanyMaterialVoterTest extends TestCase
{
    private const ATTRIBUTE = 'COMPLAINT_ON_PUBLISHED_COMPANY_MATERIAL';
    private ComplaintOnPublishedCompanyMaterialVoter $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = new ComplaintOnPublishedCompanyMaterialVoter();
    }

    public function testVoteAllowForAdminRole(): void
    {
        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForCompanyOwner(): void
    {
        $ownerUser = $this->createMockUser(1);
        $subject = $this->createMockCompanyWithOwner($ownerUser);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($ownerUser), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteDenyForCompanyEmployee(): void
    {
        $subject = $this->createMockCompanyWithEmployee();

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testVoteAllowForUser(): void
    {
        $subject = $this->createMockCompany();

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
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
            $this->voter->vote($this->getUserTokenInstance($this->createMockAdmin()), $subject, ['COMPLAINT_ON_PUBLISHED_COMPANY_MATERIAL_WRONG']),
        );
    }

    private function getUserTokenInstance(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

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
