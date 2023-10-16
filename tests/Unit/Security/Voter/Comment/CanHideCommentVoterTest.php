<?php

namespace Tests\Unit\Security\Voter\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use App\Security\Voter\Comment\CanHideCommentVoter;
use Symfony\Component\Security\Core\Security;
use Tests\Unit\TestCase;

/**
 * @group voter
 */
class CanHideCommentVoterTest extends TestCase
{
    private const ATTRIBUTE = 'CAN_HIDE_COMMENT';

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

    public function testVoteAllowForCommentForCompanyOwner(): void
    {
        $subject = $this->createMockCommentOnCompanyArticleForCompanyOwner();
        $company = $subject->getRecord()->getCompanyAuthor();
        $token = $this->getUserTokenInstance($this->createMockUser(1));

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_EDIT_COMPANY_RESOURCES', $company, true],
            ]);

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteAllowForCommentForCompanyEmployee(): void
    {
        $subject = $this->createMockCommentOnCompanyArticleForCompanyEmployee();
        $company = $subject->getRecord()->getCompanyAuthor();
        $token = $this->getUserTokenInstance($this->createMockUser(1));

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_EDIT_COMPANY_RESOURCES', $company, true],
            ]);


        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteAllowForCommentForAdministration(): void
    {
        $subject = $this->createMockComment();
        $token = $this->getUserTokenInstance($this->createMockAdministration());

        $this->security = $this->createMock(Security::class);
        $this->security
            ->method('isGranted')
            ->willReturnMap([
                ['CAN_MODERATE', $subject, true],
            ]);

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteAllowForCommentForUserContentEditor(): void
    {
        $subject = $this->createMockComment();
        $token = $this->getUserTokenInstance($this->createMockUserContentEditor());

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voterResult);
    }

    public function testVoteDenyForUser(): void
    {
        $subject = $this->createMockComment();
        $token = $this->getUserTokenInstance($this->createMockUser(1));

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voterResult);
    }

    public function testVoteWithIncorrectSubject(): void
    {
        $subject = new stdClass();
        $token = $this->getUserTokenInstance($this->createMockAdministration());

        $voterResult = $this->getVoteResult($token, $subject, [self::ATTRIBUTE]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voterResult);
    }

    public function testVoteWithIncorrectAttribute(): void
    {
        $subject = $this->createMockComment();
        $token = $this->getUserTokenInstance($this->createMockAdministration());

        $voterResult = $this->getVoteResult($token, $subject, ['VIEW_COMPANY_STATISTICS_WRONG']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $voterResult);
    }

    /**
     * @param mixed $subject
     * @param string[] $attributes
     */
    private function getVoteResult(TokenInterface $token, $subject, array $attributes): int
    {
        $voter = new CanHideCommentVoter($this->security);

        return $voter->vote($token, $subject, $attributes);
    }

    private function getUserTokenInstance(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        return $token;
    }

    private function createMockComment(): Comment
    {
        return $this->getMockBuilder(Comment::class)->disableOriginalConstructor()->getMock();
    }

    private function createMockCommentOnCompanyArticleForCompanyOwner(): Comment
    {
        $comment = $this->getMockBuilder(Comment::class)->disableOriginalConstructor()->getMock();
        $comment->method('getRecord')->willReturn($this->getMockCompanyArticleForCompanyOwner());

        return $comment;
    }

    private function createMockCommentOnCompanyArticleForCompanyEmployee(): Comment
    {
        $comment = $this->getMockBuilder(Comment::class)->disableOriginalConstructor()->getMock();
        $comment->method('getRecord')->willReturn($this->getMockCompanyArticleForCompanyEmployee());

        return $comment;
    }

    private function getMockCompanyArticleForCompanyOwner(): CompanyArticle
    {
        $companyArticle = $this->getMockBuilder(CompanyArticle::class)->disableOriginalConstructor()->getMock();
        $companyArticle->method('getCompanyAuthor')->willReturn($this->createMockCompanyWithOwner());

        return $companyArticle;
    }

    private function getMockCompanyArticleForCompanyEmployee(): CompanyArticle
    {
        $companyArticle = $this->getMockBuilder(CompanyArticle::class)->disableOriginalConstructor()->getMock();
        $companyArticle->method('getCompanyAuthor')->willReturn($this->createMockCompanyWithEmployee());

        return $companyArticle;
    }

    private function createMockCompanyWithOwner(): Company
    {
        $company = $this->getMockBuilder(Company::class)->disableOriginalConstructor()->getMock();
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
