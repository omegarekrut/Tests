<?php

namespace Tests\Functional\Domain\Comment\Command\Handler;

use App\Domain\Comment\Command\RestoreCommentThreadCommand;
use App\Domain\Comment\Entity\Comment;
use Tests\DataFixtures\ORM\Comment\LoadHiddenAnswersToAnswerComments;
use Tests\Functional\TestCase;

/**
 * @group comment
 */
class RestoreCommentThreadHandlerTest extends TestCase
{
    private Comment $answerToAnswerToComment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadHiddenAnswersToAnswerComments::class,
        ])->getReferenceRepository();

        $this->answerToAnswerToComment = $referenceRepository->getReference(LoadHiddenAnswersToAnswerComments::getRootReferenceName());
    }

    public function testAnswersToCommentHideWithRootComment(): void
    {
        $commentThread = $this->answerToAnswerToComment->getAnswers();

        $restoreCommentThreadCommand = new RestoreCommentThreadCommand($this->answerToAnswerToComment);

        $this->getCommandBus()->handle($restoreCommentThreadCommand);

        foreach ($commentThread as $comment) {
            $this->assertTrue($comment->isActive());
        }
    }
}
