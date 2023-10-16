<?php

namespace Tests\Unit\Domain\Log\Constraint;

use App\Domain\Log\Entity\Assert\LoggingActionNameRequiredAttention;
use App\Domain\Log\Validator\Constraint\ActionRequireAttention;
use App\Domain\Log\Validator\Constraint\ActionRequireAttentionValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class ActionRequireAttentionValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var ActionRequireAttentionValidator */
    private $actionRequireAttentionValidator;
    /** @var ActionRequireAttention */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->actionRequireAttentionValidator = new ActionRequireAttentionValidator();
        $this->actionRequireAttentionValidator->initialize($this->executionContext);

        $this->constraint = new ActionRequireAttention();
    }

    public function testValidationPassWhenActionNameNotDefined(): void
    {
        $this->actionRequireAttentionValidator->validate(null, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationPassWhenActionNameRequireAttention(): void
    {
        $actionName = 'Some\\DeleteAction';

        $this->assertTrue(LoggingActionNameRequiredAttention::assert($actionName));

        $this->actionRequireAttentionValidator->validate($actionName, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenActionNameNotRequireAttention(): void
    {
        $actionName = 'Some\\ViewAction';

        $this->assertFalse(LoggingActionNameRequiredAttention::assert($actionName));

        $this->actionRequireAttentionValidator->validate($actionName, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidatorNotSupportsOtherConstraints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->actionRequireAttentionValidator->validate(null, $this->createMock(Constraint::class));
    }
}
