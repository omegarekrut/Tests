<?php

namespace Tests\Unit\Module\Mailgun\WebhookEvent\Validator\Constraint;

use App\Module\Mailgun\WebhookEvent\Validator\Constraint\EventTimestampIsFresh;
use App\Module\Mailgun\WebhookEvent\Validator\Constraint\EventTimestampIsFreshValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group mailgun
 */
class EventTimestampIsFreshValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var EventTimestampIsFreshValidator */
    private $eventTimestampIsFreshValidator;
    /** @var EventTimestampIsFresh */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->eventTimestampIsFreshValidator = new EventTimestampIsFreshValidator();
        $this->eventTimestampIsFreshValidator->initialize($this->executionContext);

        $this->constraint = new EventTimestampIsFresh();
    }

    public function testValidationPassWhenFreshTimestamp(): void
    {
        $freshTimestamp = time() - 10;

        $this->eventTimestampIsFreshValidator->validate($freshTimestamp, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenTimestampIsNotFresh(): void
    {
        $notFreshTimestamp = time() - 20;

        $this->eventTimestampIsFreshValidator->validate($notFreshTimestamp, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationShouldBeSkippedForEmptyTimestamp(): void
    {
        $this->eventTimestampIsFreshValidator->validate('', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldBeSkippedForTimestampInInvalidFormat(): void
    {
        $this->eventTimestampIsFreshValidator->validate('not timestamp', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->eventTimestampIsFreshValidator->validate(null, $this->createMock(Constraint::class));
    }
}
