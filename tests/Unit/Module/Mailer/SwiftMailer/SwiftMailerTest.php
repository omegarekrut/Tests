<?php

namespace Tests\Unit\Module\Mailer\SwiftMailer;

use App\Module\Mailer\Exception\FailedSendMailToRecipientsException;
use App\Module\Mailer\SwiftMailer\SwiftMailer;
use Tests\Unit\TestCase;
use Swift_Mailer;
use Swift_Message;

/**
 * @group mailer
 */
class SwiftMailerTest extends TestCase
{
    private $lastSendMessage;
    private $lastFailedRecipients;

    public function testEmptyRecipientsException(): void
    {
        $this->expectException(\App\Module\Mailer\Exception\FailedSendMailToRecipientsException::class);
        $this->expectExceptionMessage('None of the recipients received the letter.');

        $mailer = new SwiftMailer(
            $this->getSwiftMailer(0),
            'support@example.com',
            'Administrator'
        );

        $message = new Swift_Message();

        $mailer->send($message);
    }

    public function testCanNotSendForAllRecipientsException(): void
    {
        $mailer = new SwiftMailer(
            $this->getSwiftMailer(1, ['problem@example.com']),
            'support@example.com',
            'Administrator'
        );

        $message = new Swift_Message();

        $actualException = null;
        try {
            $mailer->send($message);
        } catch (FailedSendMailToRecipientsException $exception) {
            $actualException = $exception;
        }

        $this->assertInstanceOf(FailedSendMailToRecipientsException::class, $actualException);
        $this->assertEquals(['problem@example.com'], $actualException->getRecipients());
    }

    public function testSetFieldFromForEmail(): void
    {
        $mailer = new SwiftMailer(
            $this->getSwiftMailer(1),
            'support@example.com',
            'Administrator'
        );

        $message = new Swift_Message();

        $mailer->send($message);

        $this->assertCount(1, $message->getFrom());
        $this->assertEquals(['support@example.com' => 'Administrator'], $message->getFrom());
    }

    private function getSwiftMailer($countRecipients, array $expectedFailedRecipients = []): Swift_Mailer
    {
        $mailer = $this->createMock(Swift_Mailer::class);
        $mailer->method('send')
            ->willReturnCallback(function ($message, &$failedRecipients) use ($countRecipients, $expectedFailedRecipients) {
                $this->lastSendMessage = $message;
                $this->lastFailedRecipients = $expectedFailedRecipients;
                $failedRecipients = $expectedFailedRecipients;

                return $countRecipients;
            });

        return $mailer;
    }
}
