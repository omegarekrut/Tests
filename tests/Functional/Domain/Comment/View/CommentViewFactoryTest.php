<?php

namespace Tests\Functional\Domain\Comment\View;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\View\CommentViewFactory;
use App\Domain\Record\Common\View\RecordViewMainInfoFactory;
use App\Module\Author\View\AuthorViewFactory;
use App\Util\ImageStorage\ImageTransformerFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithoutUrls;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class CommentViewFactoryTest extends TestCase
{
    public function testFillInformation(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCommentWithoutUrls::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var UserInterface $testUser */
        $testUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        /** @var Comment $comment */
        $comment = $referenceRepository->getReference(LoadCommentWithoutUrls::REFERENCE_NAME);

        /** @var UserInterface $userIsRecordAuthor */
        $userIsRecordAuthor = $comment->getRecord()->getAuthor();

        $commentViewFactory = new CommentViewFactory(
            $this->getContainer()->get(AuthorViewFactory::class),
            $this->createMock(RecordViewMainInfoFactory::class),
            $this->getContainer()->get(ImageTransformerFactory::class)
        );

        $commentView = $commentViewFactory->create($comment);

        $this->assertEquals($comment->getId(), $commentView->getId());
        $this->assertEquals($comment->getVotableId(), $commentView->getVotableId());
        $this->assertEquals($comment->getOwner(), $commentView->getOwner());
        $this->assertEquals($comment->getCreatedAt(), $commentView->getCreatedAt());
        $this->assertEquals($comment->isDeactivatedByRecordAuthor(), $commentView->isDeactivatedByRecordAuthor());
        $this->assertCount($comment->getAnswersList()->count(), $commentView->answers);
        $this->assertTrue($commentView->onUserRecord($userIsRecordAuthor));
        $this->assertFalse($commentView->onUserRecord($testUser));

        $this->assertEquals($comment->getAuthor()->getId(), $commentView->author->id);
        $this->assertEquals($comment->getAuthor()->getUsername(), $commentView->author->name);
        $this->assertEquals($comment->getAuthor()->getSubscribers(), $commentView->author->subscribers);

        $this->assertEquals($comment->getRecord()->getAuthor()->getId(), $commentView->recordAuthor->id);
        $this->assertEquals($comment->getRecord()->getAuthor()->getUsername(), $commentView->recordAuthor->name);
        $this->assertEquals($comment->getRecord()->getAuthor()->getSubscribers(), $commentView->recordAuthor->subscribers);
    }
}
