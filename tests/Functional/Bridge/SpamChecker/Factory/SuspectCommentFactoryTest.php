<?php

namespace Tests\Functional\Bridge\SpamChecker\Factory;

use App\Bridge\SpamChecker\Factory\SuspectCommentFactory;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\Functional\TestCase;

/**
 * @group spam-checker
 */
final class SuspectCommentFactoryTest extends TestCase
{
    /** @var Comment */
    private $comment;
    /** @var SuspectCommentFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
        ])->getReferenceRepository();

        $commentRepository = $this->getEntityManager()->getRepository(Comment::class);
        assert($commentRepository instanceof CommentRepository);

        $user = $referenceRepository->getReference(LoadUserWithComments::REFERENCE_NAME);
        $this->comment = $commentRepository->findLatestOwnedBy($user, 100)->first();

        $this->factory = $this->getContainer()->get(SuspectCommentFactory::class);
    }

    public function testCreatingSuspectUserFromUser(): void
    {
        $suspectComment = $this->factory->createFromComment($this->comment);

        $this->assertEquals($this->comment->getId(), $suspectComment->getId());
        $this->assertEquals($this->comment->getText(), $suspectComment->getText());
        $this->assertTrue(Carbon::instance($this->comment->getCreatedAt())->eq(Carbon::instance($suspectComment->getCreatedAt())));
        $this->assertEquals($this->comment->getAuthor()->getId(), $suspectComment->getAuthor()->getId());
    }
}