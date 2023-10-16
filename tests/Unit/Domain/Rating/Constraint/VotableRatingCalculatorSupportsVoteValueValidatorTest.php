<?php

namespace Tests\Unit\Domain\Rating\Constraint;

use App\Domain\Rating\Calculator\RatingCalculatorInterface;
use App\Domain\Rating\Validator\Constraint\VotableRatingCalculatorSupportsVoteValue;
use App\Domain\Rating\Validator\Constraint\VotableRatingCalculatorSupportsVoteValueValidator;
use App\Domain\Rating\VotableRatingInfoAwareInterface;
use App\Module\Voting\VotableInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class VotableRatingCalculatorSupportsVoteValueValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var VotableRatingCalculatorSupportsVoteValueValidator */
    private $validator;
    /** @var VotableRatingCalculatorSupportsVoteValue */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->validator = new VotableRatingCalculatorSupportsVoteValueValidator(new PropertyAccessor());
        $this->validator->initialize($this->executionContext);

        $this->constraint = new VotableRatingCalculatorSupportsVoteValue();
    }

    public function testValidationPassForValidVoteValue(): void
    {
        $supportedValueCalculator = $this->createCalculatorWithSupportedVoteStatus(true);

        $votableAndVoteValue = (object) [
            'votable' => $this->createVotableWithRatingCalculator($supportedValueCalculator),
            'voteValue' => 'vote value',
        ];

        $this->validator->validate($votableAndVoteValue, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenCalculatorNotSupportedValue(): void
    {
        $notSupportedValueCalculator = $this->createCalculatorWithSupportedVoteStatus(false);

        $votableAndVoteValue = (object) [
            'votable' => $this->createVotableWithRatingCalculator($notSupportedValueCalculator),
            'voteValue' => 'vote value',
        ];

        $this->validator->validate($votableAndVoteValue, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidatorNotSupportsOtherConstraints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->validator->validate(null, $this->createMock(Constraint::class));
    }

    public function testValidatorRequiresVotableRatingInfoAwareInVotableField(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('votableField should indicate');

        $this->validator->validate((object) [
            'votable' => $this->createMock(VotableInterface::class),
            'voteValue' => 'vote value',
        ], $this->constraint);
    }

    private function createVotableWithRatingCalculator(RatingCalculatorInterface $calculator): VotableRatingInfoAwareInterface
    {
        $stub = $this->createMock(VotableRatingInfoAwareInterface::class);
        $stub
            ->method('getRatingCalculator')
            ->willReturn($calculator);

        return $stub;
    }

    private function createCalculatorWithSupportedVoteStatus(bool $isSupportedVoteValue): RatingCalculatorInterface
    {
        $stub = $this->createMock(RatingCalculatorInterface::class);
        $stub
            ->method('isSupportsVoteValue')
            ->willReturn($isSupportedVoteValue);

        return $stub;
    }
}
