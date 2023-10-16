<?php

namespace Tests\Unit\Domain\Rating\Constraint;

use App\Domain\Rating\Validator\Constraint\OneVotePerVoter;
use App\Domain\Rating\Validator\Constraint\OneVotePerVoterValidator;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\Voting\Repository\InMemoryVotableRepository;
use App\Module\Voting\VoteStorage;
use App\Module\Voting\Repository\InMemoryVoteRepository;
use App\Module\Voting\VotableInterface;
use App\Module\Voting\VoterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class OneVotePerVoterValidatorTest extends TestCase
{
    private const VOTABLE_IP = '127.0.0.1';

    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var OneVotePerVoterValidator */
    private $oneVotePerVoterValidator;
    /** @var OneVotePerVoter */
    private $constraint;
    /** @var VoteStorage */
    private $voteStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voteStorage = new VoteStorage(new InMemoryVoteRepository(), new InMemoryVotableRepository());

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->oneVotePerVoterValidator = new OneVotePerVoterValidator($this->voteStorage, new PropertyAccessor());
        $this->oneVotePerVoterValidator->initialize($this->executionContext);

        $this->constraint = new OneVotePerVoter();
    }

    public function testValidationPassWhenUserVoteNotExists(): void
    {
        $voterAndVotable = (object) [
            'voter' => $this->createVoter(),
            'votable' => $this->createVotable(),
        ];

        $this->oneVotePerVoterValidator->validate($voterAndVotable, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenUserVoteAlreadyExists(): void
    {
        $voter = $this->createVoter();
        $votable = $this->createVotable();

        $this->voteStorage->addVote(1, $voter, $votable, self::VOTABLE_IP);

        $voterAndVotable = (object) [
            'voter' => $voter,
            'votable' => $votable,
        ];

        $this->oneVotePerVoterValidator->validate($voterAndVotable, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidatorNotSupportsOtherConstraints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');


        $this->oneVotePerVoterValidator->validate(null, $this->createMock(Constraint::class));
    }

    public function testValidatorRequiresVoterInVoterField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('voterField should indicate');

        $this->oneVotePerVoterValidator->validate((object) [
            'voter' => $this,
            'votable' => $this->createVotable(),
        ], $this->constraint);
    }

    public function testValidatorRequiresVotableInVotableField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('votableField should indicate');

        $this->oneVotePerVoterValidator->validate((object) [
            'voter' => $this->createVoter(),
            'votable' => $this,
        ], $this->constraint);
    }

    private function createVoter(): VoterInterface
    {
        $stub = $this->createMock(VoterInterface::class);
        $stub
            ->method('getId')
            ->willReturn(1);

        return $stub;
    }

    private function createVotable(): VotableInterface
    {
        $stub = $this->createMock(VotableInterface::class);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));

        return $stub;
    }
}
