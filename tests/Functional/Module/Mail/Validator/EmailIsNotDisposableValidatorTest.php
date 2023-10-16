<?php

namespace Tests\Functional\Module\Mail\Validator;

use App\Module\Mail\DisposableEmailChecker\DisposableEmailCheckerInterface;
use App\Module\Mail\Validator\Constraint\EmailIsNotDisposable;
use App\Module\Mail\Validator\Constraint\EmailIsNotDisposableValidator;
use App\Module\WhoIs\WhoIsServiceMock;
use Generator;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\ValidatorExecutionContextMock;

class EmailIsNotDisposableValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $validatorExecutionContextMock;
    private EmailIsNotDisposableValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $emailChecker = $this->getContainer()->get(DisposableEmailCheckerInterface::class);

        $this->validatorExecutionContextMock = new ValidatorExecutionContextMock();
        $this->validator = new EmailIsNotDisposableValidator($emailChecker);

        $this->validator->initialize($this->validatorExecutionContextMock);
    }

    public function testValidatePassedWithCorrectEmail(): void
    {
        $correctEmail = 'email@email.com';

        $this->validator->validate($correctEmail, new EmailIsNotDisposable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    /**
     * @dataProvider notFullEmailDataProvider
     */
    public function testValidatePassedWithNotFullEmail(string $notFullEmail): void
    {
        $this->validator->validate($notFullEmail, new EmailIsNotDisposable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }

    /**
     * @dataProvider disposableEmailDataProvider
     */
    public function testValidateFailWithDisposableEmail(string $disposableEmail): void
    {
        $this->validator->validate($disposableEmail, new EmailIsNotDisposable());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
        $this->assertStringContainsString(
            'Использование временного адреса электронной почты запрещено',
            $this->validatorExecutionContextMock->getViolationMessages()[0]
        );
    }

    public function notFullEmailDataProvider(): Generator
    {
        yield [''];

        yield ['e'];

        yield ['email'];

        yield ['email@'];

        yield ['email@a'];

        yield ['email@email'];

        yield ['email@email.'];

        yield ['email@email.c'];
    }

    public function disposableEmailDataProvider(): Generator
    {
        yield ['email@disposableVerifyMail.com'];

        yield ['email@disposableMailCheck.com'];
    }

    public function testValidateFailForEmailWithNewDomain(): void
    {
        $emailWithNewDomain = sprintf('email@%s', WhoIsServiceMock::NEW_DOMAIN);

        $this->validator->validate($emailWithNewDomain, new EmailIsNotDisposable());

        $this->assertTrue($this->validatorExecutionContextMock->hasViolations());
        $this->assertStringContainsString(
            'Использование временного адреса электронной почты запрещено',
            $this->validatorExecutionContextMock->getViolationMessages()[0]
        );
    }

    public function testValidatePassedForEmailWithOldDomain(): void
    {
        $emailWithOldDomain = 'email@old.domain';

        $this->validator->validate($emailWithOldDomain, new EmailIsNotDisposable());

        $this->assertFalse($this->validatorExecutionContextMock->hasViolations());
    }
}
