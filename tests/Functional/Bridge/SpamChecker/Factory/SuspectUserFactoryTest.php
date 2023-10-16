<?php

namespace Tests\Functional\Bridge\SpamChecker\Factory;

use App\Bridge\SpamChecker\Factory\SuspectUserFactory;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\DataFixtures\ORM\User\LoadUserWithSuspiciousLoginFromBannedAccount;
use Tests\Functional\TestCase;

/**
 * @group spam-checker
 */
final class SuspectUserFactoryTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var User */
    private $userWithSuspiciousLogin;
    /** @var SuspectUserFactory */
    private $factory;
    /** @var CommentRepository */
    private $commentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
            LoadUserWithSuspiciousLoginFromBannedAccount::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithComments::REFERENCE_NAME);
        $this->userWithSuspiciousLogin = $referenceRepository->getReference(LoadUserWithSuspiciousLoginFromBannedAccount::REFERENCE_NAME);

        $this->factory = $this->getContainer()->get(SuspectUserFactory::class);
        $this->commentRepository = $this->getEntityManager()->getRepository(Comment::class);
    }

    public function testCreatingSuspectUserFromUser(): void
    {
        $lastComment = $this->commentRepository->findLatestOwnedBy($this->user, 100)->first();
        assert($lastComment instanceof Comment);

        $suspectUser = $this->factory->createFromUser($this->user);

        $this->assertEquals($this->user->getId(), $suspectUser->getId());
        $this->assertEquals($this->user->getLogin(), $suspectUser->getLogin());
        $this->assertEquals($this->user->getEmailAddress(), $suspectUser->getEmailAddress());
        $this->assertEquals($this->user->getLastVisit()->getLastVisitIp(), $suspectUser->getIp());
        $this->assertFalse($suspectUser->isSuspicionAboutBannedOnAnotherAccount());
        $this->assertTrue(Carbon::instance($this->user->getCreatedAt())->eq(Carbon::instance($suspectUser->getCreatedAt())));
        $this->assertNotNull($suspectUser->getComments()->findById($lastComment->getId()));
    }

    public function testCreatingSuspectUserFromUserWithSuspicionAboutBannedOnAnotherAccount(): void
    {
        $suspectUser = $this->factory->createFromUser($this->userWithSuspiciousLogin);

        $this->assertTrue($suspectUser->isSuspicionAboutBannedOnAnotherAccount());
    }
}