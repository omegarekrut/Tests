<?php

namespace Tests\Unit\Module\Voting;

use App\Module\Voting\Collection\VoteCollection;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\Voting\Entity\Vote;
use App\Module\Voting\VotableInterface;
use App\Module\Voting\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class VoteCollectionTest extends TestCase
{
    public function testCollectCantBeCreatedWithNotVoteItems(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item must be instance of');

        new VoteCollection([$this]);
    }

    public function testCollectionHaveCount(): void
    {
        $votes = new VoteCollection([
            $this->createMock(Vote::class),
            $this->createMock(Vote::class),
        ]);

        $this->assertCount(2, $votes);
    }

    public function testVoteShouldBeAddedToCollectionInRightOrder(): void
    {
        $votes = new VoteCollection([
            $firstVote = $this->createMock(Vote::class),
            $this->createMock(Vote::class),
            $lastVote = $this->createMock(Vote::class),
        ]);

        $this->assertTrue($firstVote === $votes->first());
        $this->assertTrue($lastVote === $votes->last());
    }

    public function testInCollectionCanFindVoteByVoter(): void
    {
        $voter = $this->createVoter(1, 'voter name');

        $votes = new VoteCollection([
            $this->createVoteWithVoter($this->createVoter(null, 'voter name')),
            $expectedVote = $this->createVoteWithVoter($voter),
            $this->createVoteWithVoter($this->createVoter(2, 'other voter name')),
        ]);

        $this->assertTrue($expectedVote === $votes->findByVoter($voter));
    }

    public function testInCollectionCanFindVoteByVoterWithChangedName(): void
    {
        $voter = $this->createVoter(1, 'voter name');

        $votes = new VoteCollection([
            $this->createVoteWithVoter($this->createVoter(null, 'voter name')),
            $expectedVote = $this->createVoteWithVoter($this->createVoter(1, '')),
            $this->createVoteWithVoter($this->createVoter(2, 'other voter name')),
        ]);

        $this->assertTrue($expectedVote === $votes->findByVoter($voter));
    }

    public function testCollectionCanBeFilteredByValue(): void
    {
        $expectedValue = -1;

        $votes = new VoteCollection([
            $firstVote = $this->createVoteWithValue($expectedValue),
            $this->createVoteWithValue(3),
            $secondVote = $this->createVoteWithValue($expectedValue),
        ]);

        $filteredVotes = $votes->filterByValue($expectedValue);

        $this->assertFalse($filteredVotes === $votes);
        $this->assertCount(2, $filteredVotes);
        $this->assertContains($firstVote, $filteredVotes);
        $this->assertContains($secondVote, $filteredVotes);
    }

    public function testCollectionCanBeFilteredByPositiveValues(): void
    {
        $votes = new VoteCollection([
            $firstVote = $this->createPositiveVote(),
            $this->createNegativeVote(),
            $secondVote = $this->createPositiveVote(),
        ]);

        $positiveVotes = $votes->filterPositive();

        $this->assertFalse($positiveVotes === $votes);
        $this->assertCount(2, $positiveVotes);
        $this->assertContains($firstVote, $positiveVotes);
        $this->assertContains($secondVote, $positiveVotes);
    }

    public function testCollectionCanBeFilteredByNegativeValues(): void
    {
        $votes = new VoteCollection([
            $firstVote = $this->createNegativeVote(),
            $this->createPositiveVote(),
            $secondVote = $this->createNegativeVote(),
        ]);

        $negativeVotes = $votes->filterNegative();

        $this->assertFalse($negativeVotes === $votes);
        $this->assertCount(2, $negativeVotes);
        $this->assertContains($firstVote, $negativeVotes);
        $this->assertContains($secondVote, $negativeVotes);
    }

    public function testCollectionCanBeFilteredByVoter(): void
    {
        $voter = $this->createVoter(1, 'voter name');

        $votes = new VoteCollection([
            $firstVote = $this->createVoteWithVoter($voter),
            $this->createVoteWithVoter($this->createVoter(2, 'other voter name')),
            $secondVote = $this->createVoteWithVoter($voter),
        ]);

        $voterVotes = $votes->filterByVoter($voter);

        $this->assertFalse($voterVotes === $votes);
        $this->assertCount(2, $voterVotes);
        $this->assertContains($firstVote, $voterVotes);
        $this->assertContains($secondVote, $voterVotes);
    }

    public function testCollectionCanBeFilteredByVotable(): void
    {
        $votable = $this->createMock(VotableInterface::class);
        $votable->method('getVotableId')->willReturn(new VotableIdentifier('1', 'type'));

        $votes = new VoteCollection([
            $firstVote = $this->createVoteForVotable($votable),
            $this->createMock(Vote::class),
            $secondVote = $this->createVoteForVotable($votable),
        ]);

        $votesForVotable = $votes->filterByVotable($votable);

        $this->assertFalse($votesForVotable === $votes);
        $this->assertCount(2, $votesForVotable);
        $this->assertContains($firstVote, $votesForVotable);
        $this->assertContains($secondVote, $votesForVotable);
    }

    private function createVoter(?int $id = null, string $username): VoterInterface
    {
        $stub = $this->createMock(VoterInterface::class);
        $stub
            ->method('getId')
            ->willReturn($id);
        $stub
            ->method('getUsername')
            ->willReturn($username);

        return $stub;
    }

    private function createVoteWithVoter(VoterInterface $voter): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('getVoter')
            ->willReturn($voter);

        return $stub;
    }

    private function createVoteWithValue(int $value): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('getValue')
            ->willReturn($value);

        return $stub;
    }

    private function createPositiveVote(): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('isPositive')
            ->willReturn(true);
        $stub
            ->method('isNegative')
            ->willReturn(false);

        return $stub;
    }

    private function createNegativeVote(): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('isPositive')
            ->willReturn(false);
        $stub
            ->method('isNegative')
            ->willReturn(true);

        return $stub;
    }

    private function createVoteForVotable(): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('belongsToVotable')
            ->willReturn(true);

        return $stub;
    }
}
