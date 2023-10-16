<?php

namespace Tests\Unit\Module\Voting;

use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\Voting\Entity\AnonymousVoter;
use App\Module\Voting\Exception\VotableNotFoundException;
use App\Module\Voting\Repository\InMemoryVotableRepository;
use App\Module\Voting\VoteStorage;
use App\Module\Voting\Repository\InMemoryVoteRepository;
use Carbon\Carbon;
use Tests\Unit\Module\Voting\Mock\VotableMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class VoteStorageTest extends TestCase
{
    /** @var VoteStorage */
    private $voteStorage;
    /** @var InMemoryVotableRepository */
    private $inMemoryVotableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inMemoryVotableRepository = new InMemoryVotableRepository();
        $this->voteStorage = new VoteStorage(new InMemoryVoteRepository(), $this->inMemoryVotableRepository);
    }

    public function testAfterAddingVoteMustBeSavedInStorage(): void
    {
        $votable = $this->createVotable();

        $expectedVoteValue = 1;
        $expectedIp = '127.0.0.1';
        $expectedVoter = $this->createAnonymousVoter();

        $this->voteStorage->addVote($expectedVoteValue, $expectedVoter, $votable, $expectedIp);
        $votes = $this->voteStorage->getVotes($votable);

        $this->assertCount(1, $votes);

        $actualVote = $votes->first();

        $this->assertEquals($expectedVoteValue, $actualVote->getValue());
        $this->assertTrue($expectedVoter->getUsername() === $actualVote->getVoter()->getUsername());
        $this->assertEquals($expectedIp, $actualVote->getVoterIp());
    }

    public function testAfterDeletingVoteMustBeRemovedFromStorage(): void
    {
        $votable = $this->createVotable();
        $voter = $this->createAnonymousVoter();

        $vote = $this->voteStorage->addVote(1, $voter, $votable, '127.0.0.1');

        $this->voteStorage->deleteVote($vote);

        $actualVotes = $this->voteStorage->getVotes($votable);

        $this->assertCount(0, $actualVotes);
    }

    public function testVotableCanBeResolvedByVoteInVotableRepository(): void
    {
        $votable = $this->createVotable();
        $voter = $this->createAnonymousVoter();

        $this->inMemoryVotableRepository->persist($votable);
        $vote = $this->voteStorage->addVote(1, $voter, $votable, '127.0.0.1');

        $actualVotable = $this->voteStorage->getVotable($vote);

        $this->assertTrue($actualVotable === $votable);
    }

    public function testSearchShouldFailForNotExistsVotable(): void
    {
        $this->expectException(VotableNotFoundException::class);

        $votable = $this->createVotable();
        $voter = $this->createAnonymousVoter();

        // votableRepository is empty, because in memory votable repository
        $vote = $this->voteStorage->addVote(1, $voter, $votable, '127.0.0.1');

        $this->voteStorage->getVotable($vote);
    }

    public function testVotesCanBeFoundByVoter(): void
    {
        $votable = $this->createVotable();
        $voter = $this->createAnonymousVoter();

        $vote = $this->voteStorage->addVote(1, $voter, $votable, '127.0.0.1');

        $actualVotes = $this->voteStorage->getVoterVotes($voter);

        $this->assertCount(1, $actualVotes);
        $this->assertTrue($vote === $actualVotes->first());
    }

    public function testStorageMustCollectStatisticForVoter(): void
    {
        $voter = $this->createAnonymousVoter();

        $this->voteStorage->addVote(-1, $voter, $this->createVotable(), '127.0.0.1');
        $this->voteStorage->addVote(1, $voter, $this->createVotable(), '127.0.0.1');

        $statistic = $this->voteStorage->getVoterStatistic($voter);

        $this->assertEquals(1, $statistic->getNegativeVotes());
    }

    public function testFilteringVotablesWithDayOfVotingShouldWork(): void
    {
        try {
            Carbon::setTestNow(Carbon::yesterday());

            $voter = $this->createAnonymousVoter();

            $votableA = $this->createVotable();
            $votableB = $this->createVotable();

            $this->inMemoryVotableRepository->persist($votableA);
            $this->inMemoryVotableRepository->persist($votableB);

            $this->voteStorage->addVote(1, $voter, $votableA, '127.0.0.1');
            $this->voteStorage->addVote(1, $voter, $votableB, '127.0.0.1');

            Carbon::setTestNow();

            $this->voteStorage->addVote(1, $voter, $votableB, '127.0.0.1');

            $ratedVotablesFirstDay = $this->voteStorage->getRatedVotablesForDay(Carbon::yesterday());
            $this->assertEquals(2, count($ratedVotablesFirstDay));

            $ratedVotablesSecondDay = $this->voteStorage->getRatedVotablesForDay(Carbon::today());
            $this->assertEquals(1, count($ratedVotablesSecondDay));
            $this->assertTrue($votableB === $ratedVotablesSecondDay[0]);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createVotable(): VotableMock
    {
        static $votableId = 1;
        $id = new VotableIdentifier((string) $votableId++, 'type');

        return new VotableMock($id);
    }

    private function createAnonymousVoter(): AnonymousVoter
    {
        return new AnonymousVoter('some voter');
    }
}
