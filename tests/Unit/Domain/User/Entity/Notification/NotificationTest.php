<?php

namespace Tests\Unit\Domain\User\Entity\Notification;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Collection\NotificationCollection;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\User;
use App\Module\Owner\AnonymousOwner;
use App\Module\Voting\Entity\Vote;
use App\Module\Voting\VoterInterface;
use Tests\Unit\TestCase;

abstract class NotificationTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->generateUser();
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    protected function getUserFirstUnreadNotification(): Notification
    {
        return $this->user->getUnreadNotifications()->first();
    }

    protected function getUserUnreadNotification(): NotificationCollection
    {
        return $this->user->getUnreadNotifications();
    }

    protected function createArticle(User $author): Article
    {
        $stub = $this->createMock(Article::class);
        $stub
            ->method('isHidden')
            ->willReturn(false);
        $stub
            ->method('isActive')
            ->willReturn(true);
        $stub
            ->method('getAuthor')
            ->willReturn($author);

        return $stub;
    }

    protected function createUserRecord(User $user): Record
    {
        $stub = $this->createMock(Record::class);
        $stub
            ->method('getAuthor')
            ->willReturn($user);
        $stub
            ->method('isActive')
            ->willReturn(true);
        $stub
            ->method('isHidden')
            ->willReturn(false);

        return $stub;
    }

    protected function createCommentOnRecord(User $author, Record $record): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('getRecord')
            ->willReturn($record);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }

    protected function createAnswerToCommentOnRecord(User $author, Record $record, Comment $comment): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('getRecord')
            ->willReturn($record);
        $stub
            ->method('getParentComment')
            ->willReturn($comment);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }

    protected function createCompanyWithOwner(User $owner): Company
    {
        $stub = $this->createMock(Company::class);
        $stub
            ->method('getOwner')
            ->willReturn($owner);

        return $stub;
    }

    protected function createCompanyWithoutOwner(): Company
    {
        $stub = $this->createMock(Company::class);
        $stub
            ->method('getOwner')
            ->willReturn($this->createMock(AnonymousOwner::class));

        return $stub;
    }

    protected function createCompanyArticle(User $owner, Company $company): CompanyArticle
    {
        $stub = $this->createMock(CompanyArticle::class);
        $stub
            ->method('getOwner')
            ->willReturn($owner);
        $stub
            ->method('getCompanyAuthor')
            ->willReturn($company);
        $stub
            ->method('isActive')
            ->willReturn(true);
        $stub
            ->method('isHidden')
            ->willReturn(false);

        return $stub;
    }

    protected function createCompanyReview(User $author, Company $company): CompanyReview
    {
        $stub = $this->createMock(CompanyReview::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('getCompany')
            ->willReturn($company);
        $stub
            ->method('isActive')
            ->willReturn(true);
        $stub
            ->method('isHidden')
            ->willReturn(false);

        return $stub;
    }

    protected function createGallery(): Gallery
    {
        $stub = $this->createMock(Gallery::class);
        $stub
            ->method('isHidden')
            ->willReturn(false);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }

    protected function createMap(): Map
    {
        $stub = $this->createMock(Map::class);
        $stub
            ->method('isHidden')
            ->willReturn(false);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }

    protected function createComment(User $author): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }

    protected function createVote(VoterInterface $voter, bool $isBelongToVotable, bool $isPositive): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('getVoter')
            ->willReturn($voter);
        $stub
            ->method('belongsToVotable')
            ->willReturn($isBelongToVotable);
        $stub
            ->method('isPositive')
            ->willReturn($isPositive);

        return $stub;
    }

    protected function createTiding(): Tidings
    {
        $stub = $this->createMock(Tidings::class);
        $stub
            ->method('isHidden')
            ->willReturn(false);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }

    protected function createVideo(): Video
    {
        $stub = $this->createMock(Video::class);
        $stub
            ->method('isHidden')
            ->willReturn(false);
        $stub
            ->method('isActive')
            ->willReturn(true);

        return $stub;
    }
}
