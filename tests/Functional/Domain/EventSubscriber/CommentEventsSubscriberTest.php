<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Ban\Service\BanInterface as BanServiceInterface;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Event\CommentCreatedEvent;
use App\Domain\Comment\Event\CommentUpdatedEvent;
use App\Domain\Comment\Repository\CommentRepository;
use Tests\DataFixtures\ORM\Comment\LoadSpamComment;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\Functional\TestCase;

/**
 * @group comment
 * @group comment-events
 *
 * @todo need to refactor
 */
class CommentEventsSubscriberTest extends TestCase
{
    /** @var CommentRepository */
    private $commentRepository;
    /** @var Comment */
    private $spamComment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
            LoadSpamComment::class,
        ])->getReferenceRepository();

        $this->commentRepository = $this->getEntityManager()->getRepository(Comment::class);
        $this->spamComment = $referenceRepository->getReference(LoadSpamComment::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    /**
     * @group spam-detection
     */
    public function testSpamCommentAfterCreatedMustBeRemovedAndAuthorMustBeBanned(): void
    {
        $spammerAuthor = $this->spamComment->getAuthor();

        $this->getEventDispatcher()->dispatch(new CommentCreatedEvent($this->spamComment));

        $actualSpamComment = $this->commentRepository->findById($this->spamComment->getId());
        $banService = $this->getContainer()->get(BanServiceInterface::class);
        $isBanned = (bool) $banService->getBanInformationByUserId($spammerAuthor->getId());

        $this->assertNull($actualSpamComment);
        $this->assertTrue($isBanned);
    }

    /**
     * @group spam-detection
     */
    public function testSpamCommentAfterUpdatedMustBeRemovedAndAuthorMustBeBanned(): void
    {
        $spammerAuthor = $this->spamComment->getAuthor();

        $this->getEventDispatcher()->dispatch(new CommentUpdatedEvent($this->spamComment));

        $actualSpamComment = $this->commentRepository->findById($this->spamComment->getId());
        $banService = $this->getContainer()->get(BanServiceInterface::class);
        $isBanned = (bool) $banService->getBanInformationByUserId($spammerAuthor->getId());

        $this->assertNull($actualSpamComment);
        $this->assertTrue($isBanned);
    }
}
