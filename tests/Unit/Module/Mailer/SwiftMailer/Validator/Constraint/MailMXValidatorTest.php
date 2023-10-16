<?php

namespace Tests\Unit\Module\Mailer\SwiftMailer\Validator\Constraint;

use App\Module\Mail\Validator\Constraint\MailMX;
use App\Module\Mail\Validator\Constraint\MailMXValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group mailer
 */
class MailMXValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var MailMXValidator */
    private $mailMXValidator;
    /** @var MailMX */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->mailMXValidator = new MailMXValidator();
        $this->mailMXValidator->initialize($this->executionContext);

        $this->constraint = new MailMX();
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->mailMXValidator,
            $this->constraint
        );

        parent::tearDown();
    }

    public function testValidationMustBePassed(): void
    {
        $this->mailMXValidator->validate('test@fishingsib.ru', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldBeSkipped(): void
    {
        $this->mailMXValidator->validate('test', $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFail(): void
    {
        $this->mailMXValidator->validate('test@fishingsib.ruf', $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }
}
