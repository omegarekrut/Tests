<?php

namespace Tests\Unit\Security\Voter;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\UserRole;
use App\Security\Voter\CanModerateVoter;
use Generator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
final class CanModerateVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_MODERATE';

    private CanModerateVoter $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = new CanModerateVoter();
    }

    public function testVoteOnUnsupportedAttribute(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->getUserWithRole(UserRole::admin()));

        $voterValue = $this->voter->vote($token, null, ['UNSUPPORTED_ATTRIBUTE']);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voterValue);
    }

    public function testVoteOnTokenWithoutUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn(null);

        $voterValue = $this->voter->vote($token, null, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterValue);
    }

    /**
     * @dataProvider getVotedRoles
     */
    public function testVoteAllowForRole(UserRole $userRole, int $expectedVoteStatus): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->getUserWithRole($userRole));

        $voterValue = $this->voter->vote($token, null, [self::ATTRIBUTE]);

        $this->assertEquals($expectedVoteStatus, $voterValue);
    }

    public function getVotedRoles(): Generator
    {
        yield (string) UserRole::moderatorABM() => [UserRole::moderatorABM(), VoterInterface::ACCESS_DENIED];

        yield (string) UserRole::moderator() => [UserRole::moderator(), VoterInterface::ACCESS_GRANTED];

        yield (string) UserRole::admin() => [UserRole::admin(), VoterInterface::ACCESS_GRANTED];

        yield (string) UserRole::userContentEditor() => [UserRole::userContentEditor(), VoterInterface::ACCESS_DENIED];

        yield (string) UserRole::user() => [UserRole::user(), VoterInterface::ACCESS_DENIED];
    }

    private function getUserWithRole(UserRole $userRole): User
    {
        $user = $this->createPartialMock(User::class, ['getRoles']);

        $user
            ->method('getRoles')
            ->willReturn([(string) $userRole]);

        return $user;
    }
}
