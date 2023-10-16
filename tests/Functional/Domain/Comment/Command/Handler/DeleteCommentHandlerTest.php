<?php

namespace Tests\Functional\Domain\Comment\Command\Handler;

use App\Domain\Comment\Command\DeleteCommentCommand;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Rating\Command\VoteForCommentCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Comment\LoadAnswersToAnswerComments;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group comment
 */
class DeleteCommentHandlerTest extends TestCase
{
    private Record $record;
    private Comment $comment;
    private Comment $answerToAnswerToComment;
    private User $user;
    private VoteStorage $voteStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadAnswersToAnswerComments::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->comment = $this->record->getComments()->first();
        $this->answerToAnswerToComment = $referenceRepository->getReference(LoadAnswersToAnswerComments::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->comment,
            $this->answerToAnswerToComment,
            $this->user,
            $this->voteStorage
        );

        parent::tearDown();
    }

    public function testAfterHandlingCommentMustBeDelete(): void
    {
        $deleteCommentCommand = new DeleteCommentCommand($this->comment);

        $this->getCommandBus()->handle($deleteCommentCommand);

        $this->assertFalse($this->record->getComments()->contains($this->comment));
    }

    public function testAfterCommentDeletingVotesForCommentShouldBeAlsoDeleted(): void
    {
        $deleteCommentCommand = new DeleteCommentCommand($this->comment);

        $sourceComment = clone $this->comment;

        $this->voteForCommentByUser($this->comment, $this->user);

        $this->getCommandBus()->handle($deleteCommentCommand);

        $this->assertCount(0, $this->voteStorage->getVotes($sourceComment));
    }

    public function testAnswersToCommentDeletingWithRootComment(): void
    {
        $commentBranch = [];
        $parentComment = $this->answerToAnswerToComment->getParentComment();
        $rootComment = $parentComment->getParentComment();
        $record = $rootComment->getRecord();

        $commentBranch[] = clone $this->answerToAnswerToComment;
        $commentBranch[] = clone $parentComment;
        $commentBranch[] = clone $rootComment;

        $deleteCommentCommand = new DeleteCommentCommand($rootComment);

        $this->getCommandBus()->handle($deleteCommentCommand);

        foreach ($commentBranch as $comment) {
            $this->assertFalse($record->getCommentsWithAnswers()->contains($comment));
        }
    }

    private function voteForCommentByUser(Comment $comment, User $user): void
    {
        $command = new VoteForCommentCommand($comment, 1, $user, '127.0.0.1');

        $this->getCommandBus()->handle($command);
    }
}
