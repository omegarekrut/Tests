<?php

namespace Tests\Functional\Domain\Comment\Command\Handler;

use App\Domain\Comment\Command\HideCommentThreadCommand;
use App\Domain\Comment\Entity\Comment;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Comment\LoadAnswersToAnswerComments;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group comment
 */
class HideCommentThreadHandlerTest extends TestCase
{
    private Comment $answerToAnswerToComment;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAnswersToAnswerComments::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->answerToAnswerToComment = $referenceRepository->getReference(LoadAnswersToAnswerComments::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    public function testAnswersToCommentHideWithRootComment(): void
    {
        $commentThread = [];
        $parentComment = $this->answerToAnswerToComment->getParentComment();
        $rootComment = $parentComment->getParentComment();

        $commentThread[] = $this->answerToAnswerToComment;
        $commentThread[] = $parentComment;
        $commentThread[] = $rootComment;

        $hideCommentCommand = new HideCommentThreadCommand($rootComment, $this->user);

        $this->getCommandBus()->handle($hideCommentCommand);

        foreach ($commentThread as $comment) {
            $this->assertFalse($comment->isActive());
        }
    }
}
