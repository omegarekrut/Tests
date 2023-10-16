<?php

namespace Tests\Functional\Domain\Rating\Command;

use App\Domain\Rating\Command\VoteForRecordCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithHighRating;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRating;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRatingRegisteredMoreThanTwoYearsAgo;
use Tests\Functional\ValidationTestCase;

/**
 * @group rating
 */
class VoteForRecordCommandValidationTest extends ValidationTestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadUserWithHighRating::class,
            LoadUserWithoutRating::class,
            LoadUserWithoutRatingRegisteredMoreThanTwoYearsAgo::class,
            LoadArticles::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testVoterCanOnlyVoteOnceForRecord(): void
    {
        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $this->voteForRecord(1, $article, $voter);

        $command = new VoteForRecordCommand($article, 1, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('voter', 'Вы уже голосовали и ваш голос учтен.');
    }

    public function testVoterCantVoteForSelfRecord(): void
    {
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $command = new VoteForRecordCommand($article, 1, $article->getAuthor(), '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('voter', 'Вы не можете голосовать за собственную запись.');
    }

    public function testVoteValueMustBeOnlyLikeOrDislike(): void
    {
        $fiveStartsVoteValue = 5;

        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $command = new VoteForRecordCommand($article, $fiveStartsVoteValue, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('voteValue', 'Значение голоса не поддерживается.');
    }

    public function testVoterWithLowRatingCantVote(): void
    {
        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadUserWithoutRating::REFERENCE_NAME);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $command = new VoteForRecordCommand($article, 1, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertFieldInvalid(
            'voter',
            'Для голосования ваш рейтинг должен быть выше 5'
        );
    }

    public function testOldVoterWithLowRatingCanVote(): void
    {
        /** @var User $oldVoter */
        $oldVoter = $this->referenceRepository->getReference(LoadUserWithoutRatingRegisteredMoreThanTwoYearsAgo::REFERENCE_NAME);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $command = new VoteForRecordCommand($article, 1, $oldVoter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        /** @var User $voter */
        $voter = $this->referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);
        /** @var Article $article */
        $article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $command = new VoteForRecordCommand($article, 1, $voter, '127.0.0.1');
        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    private function voteForRecord(float $voteValue, Record $record, User $voter): void
    {
        $command = new VoteForRecordCommand($record, $voteValue, $voter, '127.0.0.1');

        $this->getCommandBus()->handle($command);
    }
}
