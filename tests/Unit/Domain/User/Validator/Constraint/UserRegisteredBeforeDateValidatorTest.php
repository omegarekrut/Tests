<?php

namespace Tests\Unit\Domain\User\Validator\Constraint;

use App\Domain\User\Validator\Constraint\UserRegisteredBeforeDate;
use App\Domain\User\Validator\Constraint\UserRegisteredBeforeDateValidator;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class UserRegisteredBeforeDateValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;

    /** @var UserRegisteredBeforeDateValidator */
    private $userRegisteredBeforeDateValidator;

    /** @var UserRegisteredBeforeDate */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->userRegisteredBeforeDateValidator = new UserRegisteredBeforeDateValidator();
        $this->userRegisteredBeforeDateValidator->initialize($this->executionContext);

        $this->constraint = new UserRegisteredBeforeDate();
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->userRegisteredBeforeDateValidator,
            $this->constraint
        );

        parent::tearDown();
    }

    public function testValidationMustPassIfUserRegisteredBeforeDate(): void
    {
        $user = $this->createUserRegisteredOnDate(Carbon::now()->subDay());

        $this->constraint->dateTimeAsString = '1 day ago';
        $this->userRegisteredBeforeDateValidator->validate($user, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }


    public function testValidationMustFailIfUserRegisteredAfterDate(): void
    {
        $user = $this->createUserRegisteredOnDate(Carbon::now());

        $this->constraint->dateTimeAsString = '1 day ago';
        $this->userRegisteredBeforeDateValidator->validate($user, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidatorNotSupportsOtherConstraints(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->userRegisteredBeforeDateValidator->validate(null, $this->createMock(Constraint::class));
    }

    public function testValidatorDoesNotSupportConstraintWithEmptyDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date time must be defined as string');

        $this->constraint->dateTimeAsString = '';

        $this->userRegisteredBeforeDateValidator->validate($this, $this->constraint);
    }

    public function testValidatorDoesNotSupportConstraintWithInvalidDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date time must be valid and in the correct format');

        $this->constraint->dateTimeAsString = 'invalid date time as string';

        $this->userRegisteredBeforeDateValidator->validate($this, $this->constraint);
    }

    private function createUserRegisteredOnDate(DateTimeInterface $createdAt): User
    {
        return $this->createConfiguredMock(User::class, [
            'getCreatedAt' => $createdAt,
        ]);
    }
}
