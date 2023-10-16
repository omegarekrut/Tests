<?php

namespace Tests\Functional\Domain\Rating\Command;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Rating\Command\VoteForCommentCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group rating
 */
class VoteForCommentCommandValidatorTest extends ValidationTestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadArticles::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testVoterCanOnlyVoteOnceForComment(): void
    {
        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $comment = $article->getComments()->first();

        $this->voteForComment(1, $comment, $voter);

        $command = new VoteForCommentCommand($comment, 1, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('voter', 'Вы уже голосовали и ваш голос учтен.');
    }

    public function testVoterCantVoteForSelfComment(): void
    {
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $comment = $article->getComments()->first();

        $command = new VoteForCommentCommand($comment, 1, $comment->getAuthor(), '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('voter', 'Вы не можете голосовать за собственный комментарий.');
    }

    public function testVoteValueMustBeOnlyLikeOrDislike(): void
    {
        $fiveStartsVoteValue = 5;

        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $comment = $article->getComments()->first();

        $command = new VoteForCommentCommand($comment, $fiveStartsVoteValue, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('voteValue', 'Значение голоса не поддерживается.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $comment = $article->getComments()->first();

        $command = new VoteForCommentCommand($comment, 1, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    private function voteForComment(float $voteValue, Comment $comment, User $voter): void
    {
        $command = new VoteForCommentCommand($comment, $voteValue, $voter, '127.0.0.1');

        $this->getCommandBus()->handle($command);
    }
}
