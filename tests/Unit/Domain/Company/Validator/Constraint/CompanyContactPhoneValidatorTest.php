<?php

namespace Tests\Unit\Domain\Company\Validator\Constraint;

use App\Domain\Company\Validator\Constraint\CompanyContactPhone;
use App\Domain\Company\Validator\Constraint\CompanyContactPhoneValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group company
 */
class CompanyContactPhoneValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private CompanyContactPhone $phoneConstraint;
    private CompanyContactPhoneValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phoneConstraint = new CompanyContactPhone();
        $this->executionContext = new ValidatorExecutionContextMock();
        $this->validator = new CompanyContactPhoneValidator();

        $this->validator->initialize($this->executionContext);
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->phoneConstraint,
            $this->validator
        );

        parent::tearDown();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->validator->validate('phone number', $this->createMock(Constraint::class));
    }

    public function testValidationMustBeSkippedForNullValue(): void
    {
        $this->validator->validate(null, $this->phoneConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    /** @dataProvider validPhoneProvider */
    public function testGoodPhonesValidation(string $phone): void
    {
        $this->validator->validate($phone, $this->phoneConstraint);

        $this->assertNotContains($this->phoneConstraint->messageInvalidMask, $this->executionContext->getViolationMessages());
    }

    public function validPhoneProvider(): array
    {
        return [
            ['+7 (000) 000-00-00'],
            ['+7 (999) 606-69-42'],
            ['+7 (111) 123-45-67'],
            ['+7 (999) 606-69-42'],
        ];
    }

    /** @dataProvider invalidPhoneProvider */
    public function testBadPhonesPhonesValidation(string $phone): void
    {
        $this->validator->validate($phone, $this->phoneConstraint);

        $this->assertContains($this->phoneConstraint->messageInvalidMask, $this->executionContext->getViolationMessages());
    }

    public function invalidPhoneProvider(): array
    {
        return [
            ['+8 (000) 000 00-00'],
            ['+8 (000) 000 00-00 '],
            ['+7  (999) 606 69-42'],
            ['+7 ((111) 123 45-67'],
            ['+7 (999) 606 69-42 '],
            ['+7 (999)  606 69-42'],
            ['+7 (999) 606  69-42'],
            ['+7 (999) 606 69 -42'],
            ['+7 (999) 606 69- 42'],
            ['+7 (999) 606 69 - 42'],
            ['+7 (999)-606-69-42'],
        ];
    }
}
