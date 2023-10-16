<?php

namespace Tests\Unit\Domain\SpamBlockList\Validator\Constraint;

use App\Domain\SpamBlockList\Entity\SpamEmail;
use App\Domain\SpamBlockList\Repository\SpamEmailRepository;
use App\Domain\SpamBlockList\Validator\Constraint\EmailIsNotInSpamBlockList;
use App\Domain\SpamBlockList\Validator\Constraint\EmailIsNotInSpamBlockListValidator;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group spam-block-list
 */
class EmailIsNotInSpamBlockListValidatorTest extends TestCase
{
    private const SPAM_EMAIL = 'spam-email@gmail.com';
    private const TRUSTED_EMAIL = 'trusted-email@gmail.com';

    /** @var EmailIsNotInSpamBlockList */
    private $constraint;

    /** @var EmailIsNotInSpamBlockListValidator */
    private $emailIsNotInSpamBlockListValidator;

    /** @var ValidatorExecutionContextMock */
    private $executionContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new EmailIsNotInSpamBlockList();

        $spamEmailRepositoryMock = $this->createMock(SpamEmailRepository::class);
        $spamEmailRepositoryMock->method('findByEmail')
            ->will($this->returnValueMap([
                [self::SPAM_EMAIL, new SpamEmail(self::SPAM_EMAIL)],
                [self::TRUSTED_EMAIL, null],
            ]));
        $this->emailIsNotInSpamBlockListValidator = new EmailIsNotInSpamBlockListValidator($spamEmailRepositoryMock);

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->emailIsNotInSpamBlockListValidator->initialize($this->executionContext);
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->emailIsNotInSpamBlockListValidator,
            $this->constraint
        );

        parent::tearDown();
    }

    public function testValidationSkippedIfEmailIsNull(): void
    {
        $this->emailIsNotInSpamBlockListValidator->validate(null, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidatorNotSupportAnotherConstraintType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Constraint must be instance of %s',
            EmailIsNotInSpamBlockList::class
        ));

        $this->emailIsNotInSpamBlockListValidator->validate(
            self::TRUSTED_EMAIL,
            $this->createMock(Constraint::class)
        );
    }

    public function testValidationFailIfEmailIsInTheSpamBlockList(): void
    {
        $this->emailIsNotInSpamBlockListValidator->validate(self::SPAM_EMAIL, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationPassWhenEmailIsNotInTheSpamBlockList(): void
    {
        $this->emailIsNotInSpamBlockListValidator->validate(self::TRUSTED_EMAIL, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }
}
