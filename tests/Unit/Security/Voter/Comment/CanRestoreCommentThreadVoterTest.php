<?php

namespace Tests\Unit\Security\Voter\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\User\Entity\User;
use App\Security\Voter\Comment\CanRestoreCommentThreadVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class CanRestoreCommentThreadVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_RESTORE_COMMENT_THREAD';

    private CanRestoreCommentThreadVoter $voter;
    private Security $security;

    protected function setUp(): void
    {
        parent::setUp();

        $this->security = $this->createMock(Security::class);

        $this->voter = new CanRestoreCommentThreadVoter($this->security);
    }

    public function testGrantCascadeRestoreForCommentWithoutAnswers(): void
    {
        $subject = $this->createMock(Comment::class);
        $subject
            ->method('getAnswers')
            ->willReturn(new ArrayCollection([]));

        $this->security
            ->method('isGranted')
            ->willReturnOnConsecutiveCalls(true);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testDeniedCascadeRestoreForCommentIsNotAllowedForRestore(): void
    {
        $subject = $this->createMock(Comment::class);
        $subject
            ->method('getAnswers')
            ->willReturn(new ArrayCollection([]));

        $this->security
            ->method('isGranted')
            ->willReturnOnConsecutiveCalls(false);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testGrantCascadeRestoreForCommentWithAnswers(): void
    {
        $firstAnswer = $this->createMock(Comment::class);
        $secondAnswer = $this->createMock(Comment::class);

        $subject = $this->createMock(Comment::class);
        $subject
            ->method('getAnswers')
            ->willReturn(new ArrayCollection([
                $firstAnswer,
                $secondAnswer,
            ]));

        $this->security
            ->method('isGranted')
            ->willReturnOnConsecutiveCalls(true, true, true);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
        );
    }

    public function testDeniedCascadeRestoreForCommentWithAnswersWithoutAllowRestore(): void
    {
        $firstAnswer = $this->createMock(Comment::class);
        $secondAnswer = $this->createMock(Comment::class);

        $subject = $this->createMock(Comment::class);
        $subject
            ->method('getAnswers')
            ->willReturn(new ArrayCollection([
                $firstAnswer,
                $secondAnswer,
            ]));

        $this->security
            ->method('isGranted')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->getUserTokenInstance($this->createMockUser(1)), $subject, [self::ATTRIBUTE]),
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
}
