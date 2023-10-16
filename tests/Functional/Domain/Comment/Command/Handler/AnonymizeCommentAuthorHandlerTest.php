<?php

namespace Tests\Functional\Domain\Comment\Command\Handler;

use App\Domain\Comment\Command\AnonymizeCommentAuthorCommand;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\User\Entity\User;
use App\Module\Author\AnonymousAuthor;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\Functional\TestCase;

class AnonymizeCommentAuthorHandlerTest extends TestCase
{
    public function testAfterHandlingCommentMustBeAnonymous(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUserWithComments::REFERENCE_NAME);

        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->getEntityManager()->getRepository(Comment::class);

        /** @var Comment $comment */
        $comment = $commentRepository->findLatestOwnedBy($user, 1)->first();

        $this->assertInstanceOf(User::class, $comment->getAuthor());

        $this->getCommandBus()->handle(new AnonymizeCommentAuthorCommand($comment));

        $this->assertInstanceOf(AnonymousAuthor::class, $comment->getAuthor());
        $this->assertEquals($user->getUsername(), $comment->getAuthor()->getUsername());
    }
}
