<?php

namespace Tests\Unit\Security\Voter\Record;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use App\Security\Voter\Record\CanEditRecordVoter;
use DateInterval;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class CanEditRecordVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_EDIT_RECORD';

    /**
     * @var Security
     */
    private MockObject $security;

    protected function setUp(): void
    {
        parent::setUp();

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturn(false);
    }

    public function testVoteAllowForRecordOwner(): void
    {
        $ownerUser = $this->createMockUser(1);

        $subject = $this->createMockRecordWithOwner($ownerUser, new DateTime());
        $token = $this->getUserTokenInstance($ownerUser);

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteDeniedForOldRecordOwner(): void
    {
        $today = new DateTime();
        $moreThanMonthAgo = $today->sub(new DateInterval('P32D'));

        $ownerUser = $this->createMockUser(1);

        $subject = $this->createMockRecordWithOwner($ownerUser, $moreThanMonthAgo);
        $token = $this->getUserTokenInstance($ownerUser);

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testVoteAllowForAdminRole(): void
    {
        $subject = $this->createMockRecord();

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_MODERATE', $subject, true],
            ]);

        $token = $this->getUserTokenInstance($this->createMockAdministration());

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteAllowForUserContentEditor(): void
    {
        $subject = $this->createMockRecord();
        $token = $this->getUserTokenInstance($this->createMockUserContentEditor());

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteDenyForUser(): void
    {
        $subject = $this->createMockRecord();
        $token = $this->getUserTokenInstance($this->createMockUser(1));

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testVoteWithIncorrectSubject(): void
    {
        $subject = new stdClass();
        $token = $this->getUserTokenInstance(null);

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voterResult);
    }

    public function testVoteWithIncorrectAttribute(): void
    {
        $subject = $this->createMockRecord();
        $token = $this->getUserTokenInstance($this->createMockAdministration());

        $voterResult = $this->getVoteResult($token, $subject, ['CAN_EDIT_RECORD_WRONG']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voterResult);
    }

    /**
     * @param mixed $subject
     * @param string[] $attributes
     */
    private function getVoteResult(TokenInterface $token, $subject, array $attributes): int
    {
        $voter = new CanEditRecordVoter($this->security);

        return $voter->vote($token, $subject, $attributes);
    }

    private function getUserTokenInstance(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        return $token;
    }

    private function createMockRecord(): Record
    {
        $createdAt = new DateTime();
        $record = $this->getMockBuilder(Record::class)->disableOriginalConstructor()->getMock();
        $record
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $record;
    }

    private function createMockRecordWithOwner(User $author, DateTimeInterface $createdAt): Record
    {
        $article = $this->getMockBuilder(Record::class)->disableOriginalConstructor()->getMock();

        $article
            ->method('getOwner')
            ->willReturn($author);

        $article
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $article;
    }

    private function createMockUser(int $id): User
    {
        return $this->createConfiguredMock(User::class, [
            'getId' => $id,
        ]);
    }

    private function createMockAdministration(): User
    {
        return $this->createConfiguredMock(User::class, [
            'hasAdminRole' => true,
        ]);
    }

    private function createMockUserContentEditor(): User
    {
        return $this->createConfiguredMock(User::class, [
            'hasUserContentEditorRole' => true,
        ]);
    }
}
