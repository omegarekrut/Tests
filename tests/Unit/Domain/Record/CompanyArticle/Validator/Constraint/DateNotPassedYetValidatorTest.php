<?php

namespace Tests\Unit\Domain\Record\CompanyArticle\Validator\Constraint;

use App\Domain\Record\CompanyArticle\Validator\Constraint\DateNotPassedYet;
use App\Domain\Record\CompanyArticle\Validator\Constraint\DateNotPassedYetValidator;
use Carbon\Carbon;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group company
 */
class DateNotPassedYetValidatorTest extends TestCase
{
    /**
     * @var ValidatorExecutionContextMock
     */
    private $validatorExecutionContextMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validatorExecutionContextMock = new ValidatorExecutionContextMock();
    }

    protected function tearDown(): void
    {
        unset($this->validatorExecutionContextMock);

        parent::tearDown();
    }

    public function testValidationShouldFailIfPastDate(): void
    {
        $validator = new DateNotPassedYetValidator();
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate(Carbon::now()->subWeek(), new DateNotPassedYet());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldBePassedIfDateNotArrived(): void
    {
        $validator = new DateNotPassedYetValidator();
        $validator->initialize($this->validatorExecutionContextMock);

        $validator->validate(Carbon::now()->addWeek(), new DateNotPassedYet());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $validator = new DateNotPassedYetValidator();
        $validator->validate(null, $this->createMock(Constraint::class));
    }
}
