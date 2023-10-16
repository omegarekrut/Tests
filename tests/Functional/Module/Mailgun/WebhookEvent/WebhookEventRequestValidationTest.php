<?php

namespace Tests\Functional\Module\Mailgun\WebhookEvent;

use App\Module\Mailgun\WebhookEvent\Signature\SignatureGenerator;
use App\Module\Mailgun\WebhookEvent\WebhookEventRequest;
use Tests\Functional\ValidationTestCase;

/**
 * @group mailgun
 */
class WebhookEventRequestValidationTest extends ValidationTestCase
{
    /** @var WebhookEventRequest */
    private $webhookEventRequest;
    /** @var SignatureGenerator */
    private $signatureGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookEventRequest = new WebhookEventRequest();
        $this->signatureGenerator = $this->getContainer()->get(SignatureGenerator::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->webhookEventRequest,
            $this->signatureGenerator
        );

        parent::tearDown();
    }

    public function testSignatureAndEventDataRequired(): void
    {
        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertFieldInvalid('eventData', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('signature', 'Значение не должно быть пустым.');
    }

    public function testAllSignatureAttributesRequired(): void
    {
        $this->webhookEventRequest->signature = [
            'timestamp' => '',
            'token' => '',
            'signature' => '',
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        foreach (array_keys($this->webhookEventRequest->signature) as $signatureAttributeName) {
            $this->assertFieldInvalid(sprintf('signature[%s]', $signatureAttributeName), 'Значение не должно быть пустым.');
        }
    }

    public function testAllRecipientAndEventTypeRequired(): void
    {
        $this->webhookEventRequest->eventData = [
            'event' => '',
            'recipient' => '',
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertFieldInvalid('eventData[event]', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('eventData[recipient]', 'Значение не должно быть пустым.');
    }

    public function testEventTypeShouldBeOnOfKnownTypes(): void
    {
        $this->webhookEventRequest->eventData = [
            'event' => 'unknown',
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertFieldInvalid('eventData[event]', 'Выбранное Вами значение недопустимо.');
    }

    public function testRecipientEmailMustBeInCorrectFormat(): void
    {
        $this->webhookEventRequest->eventData = [
            'recipient' => 'invalid-email',
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertFieldInvalid('eventData[recipient]', 'Значение адреса электронной почты недопустимо.');
    }

    public function testSignatureTimestampMustBeFresh(): void
    {
        $this->webhookEventRequest->signature = [
            'timestamp' => time() - 60,
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertFieldInvalid('signature[timestamp]', 'Событие устарело.');
    }

    public function testSignatureMustBeValid(): void
    {
        $this->webhookEventRequest->signature = [
            'timestamp' => time(),
            'token' => 'some token',
            'signature' => 'invalid signature',
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertFieldInvalid('signature', 'Неверная подпись события.');
    }

    public function testValidationShouldBeFailForCorrectCredentialsButIncorrectTimestamp(): void
    {
        $this->webhookEventRequest->eventData = [
            'event' => 'failed',
            'recipient' => 'valid@email.com',
        ];

        $timestamp = time();
        $token = 'some-token';

        $this->webhookEventRequest->signature = [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $this->signatureGenerator->generate($token, $timestamp),
        ];

        $this->getValidator()->validate($this->webhookEventRequest);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
