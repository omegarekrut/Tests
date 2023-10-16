<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Entity\ValueObject\Rating;
use App\Domain\User\Validator\Constraint\UserHasSufficientRating;
use App\Domain\User\Validator\Constraint\UserHasSufficientRatingValidator;
use App\Domain\User\Entity\User;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class UserHasSufficientRatingValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private UserHasSufficientRatingValidator $userHasSufficientRatingValidator;
    private UserHasSufficientRating $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->userHasSufficientRatingValidator = new UserHasSufficientRatingValidator();
        $this->userHasSufficientRatingValidator->initialize($this->executionContext);

        $this->constraint = new UserHasSufficientRating();
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->userHasSufficientRatingValidator,
            $this->constraint
        );

        parent::tearDown();
    }

    /**
     * @dataProvider getRatingValuesGreaterOrEqualsOne
     */
    public function testValidationPassWhenUserRatingIsLargeEnough(int $userRating): void
    {
        $user = $this->createUserWithRating($userRating);

        $this->constraint->sufficientRating = 1;
        $this->userHasSufficientRatingValidator->validate($user, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    /**
     * @return int[][]
     */
    public function getRatingValuesGreaterOrEqualsOne(): array
    {
        return [
            [1],
            [2],
            [100],
        ];
    }

    /**
     * @dataProvider getRatingValuesLessOne
     */
    public function testValidationFailWhenUserRatingIsSmall(int $userRating): void
    {
        $user = $this->createUserWithRating($userRating);

        $this->constraint->sufficientRating = 1;
        $this->userHasSufficientRatingValidator->validate($user, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    /**
     * @return int[][]
     */
    public function getRatingValuesLessOne(): array
    {
        return [
            [0],
        ];
    }

    public function testValidatorNotSupportsOtherConstraints(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->userHasSufficientRatingValidator->validate(null, $this->createMock(Constraint::class));
    }

    public function testValidatorDoesntWorkingWithoutUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject must be instance of');

        $this->userHasSufficientRatingValidator->validate($this, $this->constraint);
    }

    public function testSufficientRatingShouldBeNumericForValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sufficientRating must be numeric');

        $this->constraint->sufficientRating = 'invalid sufficient rating';

        $this->userHasSufficientRatingValidator->validate($this->createUserWithRating(0), $this->constraint);
    }

    private function createUserWithRating(int $rating): User
    {
        $mockUser = $this->createMock(User::class);

        $mockUser
            ->method('getGlobalRating')
            ->willReturn(new Rating($rating));

        return $mockUser;
    }
}
