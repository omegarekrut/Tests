<?php

namespace Tests\Unit\Module\Mailgun\WebhookEvent\Validator\Constraint;

use App\Module\Mailgun\WebhookEvent\Signature\SignatureGenerator;
use App\Module\Mailgun\WebhookEvent\Validator\Constraint\MailgunSignatureIsValid;
use App\Module\Mailgun\WebhookEvent\Validator\Constraint\MailgunSignatureIsValidValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group mailgun
 */
class MailgunSignatureIsValidValidatorTest extends TestCase
{
    /** @var ValidatorExecutionContextMock */
    private $executionContext;
    /** @var MailgunSignatureIsValidValidator */
    private $mailgunSignatureIsValidValidator;
    /** @var MailgunSignatureIsValid */
    private $constraint;
    /** @var SignatureGenerator */
    private $signatureGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $objectContext = (object) [
            'timestamp' => time(),
            'token' => 'some token',
        ];

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->executionContext->setObject($objectContext);

        $this->signatureGenerator = $this->createSignatureGenerator();

        $this->mailgunSignatureIsValidValidator = new MailgunSignatureIsValidValidator($this->signatureGenerator);
        $this->mailgunSignatureIsValidValidator->initialize($this->executionContext);

        $this->constraint = new MailgunSignatureIsValid();
    }

    public function testValidationPassWhenSignatureAreCorrect(): void
    {
        $timestamp = time();
        $token = 'some token';
        $signature = $this->signatureGenerator->generate($token, $timestamp);

        $validSignatureAttributes = [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
        ];

        $this->mailgunSignatureIsValidValidator->validate($validSignatureAttributes, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailWhenSignatureAreNotCorrect(): void
    {
        $invalidSignatureAttributes = [
            'timestamp' => time(),
            'token' => 'some token',
            'signature' => 'invalid signature',
        ];

        $this->mailgunSignatureIsValidValidator->validate($invalidSignatureAttributes, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationShouldBeSkippedForEmptySignature(): void
    {
        $this->mailgunSignatureIsValidValidator->validate(null, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldBeSkippedForEmptySignatureAttributesFields(): void
    {
        $emptySignatureAttributes = ['timestamp' => '', 'token' => '', 'signature' => ''];

        $this->mailgunSignatureIsValidValidator->validate($emptySignatureAttributes, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationShouldFailForUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance');

        $this->mailgunSignatureIsValidValidator->validate(null, $this->createMock(Constraint::class));
    }

    private function createSignatureGenerator(): SignatureGenerator
    {
        $stub = $this->createMock(SignatureGenerator::class);
        $stub
            ->method('generate')
            ->willReturn('simple-signature');

        return $stub;
    }
}
