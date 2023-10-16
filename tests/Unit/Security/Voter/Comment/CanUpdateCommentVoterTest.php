<?php

namespace Tests\Unit\Security\Voter\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use App\Security\Voter\Comment\CanUpdateCommentVoter;
use DateInterval;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class CanUpdateCommentVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_UPDATE_COMMENT';

    /**
     * @var Security
     */
    private MockObject $security;

    /**
     * @var CommentRepository
     */
    private MockObject $commentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = $this->createMock(CommentRepository::class);

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturn(false);
    }

    public function testDeniedForAnonymousUserToken(): void
    {
        $comment = $this->getComment(Uuid::uuid4(), new DateTime());

        $token = $this->getUserTokenInstance(null);

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testGrantedForAdministration(): void
    {
        $user = $this->createMock(User::class);

        $comment = $this->getOldComment();

        $token = $this->getUserTokenInstance($user);

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_MODERATE', $comment, true],
            ]);

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testGrantedForUserContentEditor(): void
    {
        $user = $this->createMock(User::class);
        $user
            ->method('hasUserContentEditorRole')
            ->willReturn(true);

        $comment = $this->getOldComment();

        $token = $this->getUserTokenInstance($user);

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testDeniedEditOldCommentForUser(): void
    {
        $comment = $this->getOldComment();

        $token = $this->getUserTokenInstance($this->createMock(User::class));

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testDeniedEditNotLastRecordCommentsForUser(): void
    {
        $comment = $this->getComment(Uuid::uuid4(), new DateTime());
        $lastComment = $this->getComment(Uuid::uuid4(), new DateTime());

        $this->commentRepository
            ->method('findByMemberGroup')
            ->willReturn($lastComment);

        $token = $this->getUserTokenInstance($this->createMock(User::class));

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testGrantedEditForOwner(): void
    {
        $commentOwner = $this->createMock(User::class);

        $comment = $this->getComment(Uuid::uuid4(), new DateTime());
        $comment
            ->method('isOwnedBy')
            ->willReturn(true);

        $token = $this->getUserTokenInstance($commentOwner);

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testDeniedEditCommentWithoutCompanyAuthorForNotOwner(): void
    {
        $commentOwner = $this->createMock(User::class);
        $commentOwner
            ->method('getId')
            ->willReturn(42);

        $comment = $this->getComment(Uuid::uuid4(), new DateTime());
        $comment
            ->method('getOwner')
            ->willReturn($commentOwner);

        $token = $this->getUserTokenInstance($this->createMock(User::class));

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testGrantedEditForCompanyOwner(): void
    {
        $company = $this->createMock(Company::class);
        $company
            ->method('isOwnedByUser')
            ->willReturn(true);

        $record = $this->createMock(Record::class);
        $record
            ->method('getCompanyAuthor')
            ->willReturn($company);

        $comment = $this->getComment(Uuid::uuid4(), new DateTime());
        $comment
            ->method('onUserRecord')
            ->willReturn(false);
        $comment
            ->method('getRecord')
            ->willReturn($record);

        $token = $this->getUserTokenInstance($this->createMock(User::class));

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_EDIT_COMPANY_RESOURCES', $company, true],
            ]);

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testGrantedEditForCompanyEditor(): void
    {
        $company = $this->createMock(Company::class);
        $company
            ->method('isOwnedByUser')
            ->willReturn(false);
        $company
            ->method('isUserCompanyEditor')
            ->willReturn(true);

        $record = $this->createMock(Record::class);
        $record
            ->method('getCompanyAuthor')
            ->willReturn($company);

        $comment = $this->getComment(Uuid::uuid4(), new DateTime());
        $comment
            ->method('onUserRecord')
            ->willReturn(false);
        $comment
            ->method('getRecord')
            ->willReturn($record);

        $token = $this->getUserTokenInstance($this->createMock(User::class));

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_EDIT_COMPANY_RESOURCES', $company, true],
            ]);

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testDeniedEditForNotCompanyEditorOrOwner(): void
    {
        $company = $this->createMock(Company::class);
        $company
            ->method('isOwnedByUser')
            ->willReturn(false);
        $company
            ->method('isUserCompanyEditor')
            ->willReturn(false);

        $record = $this->createMock(Record::class);
        $record
            ->method('getCompanyAuthor')
            ->willReturn($company);

        $commentOwner = $this->createMock(User::class);
        $commentOwner
            ->method('getId')
            ->willReturn(42);

        $comment = $this->getComment(Uuid::uuid4(), new DateTime());
        $comment
            ->method('getOwner')
            ->willReturn($commentOwner);
        $comment
            ->method('getRecord')
            ->willReturn($record);

        $token = $this->getUserTokenInstance($this->createMock(User::class));

        $voterResult = $this->getVoteResult($token, $comment, [self::ATTRIBUTE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    /**
     * @param mixed $subject
     * @param string[] $attributes
     */
    private function getVoteResult(TokenInterface $token, $subject, array $attributes): int
    {
        $voter = new CanUpdateCommentVoter($this->commentRepository, $this->security);

        return $voter->vote($token, $subject, $attributes);
    }

    private function getUserTokenInstance(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        return $token;
    }

    private function getComment(UuidInterface $uuid, DateTimeInterface $createdAt): Comment
    {
        $comment = $this->createMock(Comment::class);

        $comment
            ->method('getId')
            ->willReturn($uuid);

        $comment
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $comment;
    }

    private function getOldComment(): Comment
    {
        $today = new DateTime();
        $moreThanMonthAgo = $today->sub(new DateInterval('P32D'));

        return $this->getComment(Uuid::uuid4(), $moreThanMonthAgo);
    }
}
