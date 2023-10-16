<?php

namespace Tests\Unit\Module\BulkMailSender\Mailgun;

use App\Module\BulkMailSender\Mailgun\MailgunClient;
use App\Module\BulkMailSender\TransferObject\Message;
use App\Module\BulkMailSender\TransferObject\Recipient;
use Exception;
use Mailgun\Mailgun;
use Tests\Unit\LoggerMock as Logger;
use Tests\Unit\Mock\BulkMailSender\Mailgun\MessagesApiMock;
use Tests\Unit\TestCase;

class MailgunClientTest extends TestCase
{
    public function testSendMessage(): void
    {
        $messagesApi = new MessagesApiMock();
        $logger = new Logger();

        $mailgunClient = new MailgunClient('some.domain', $this->createMailgunMock($messagesApi), $logger);

        $recipients = [
            new Recipient('to@example.com', ['some_field' => 'value']),
        ];
        $message = new Message('from@example.com', 'some subject', 'some body');

        $mailgunClient->sendMessage($recipients, $message);

        $this->assertCount(1, $messagesApi->getSentMessages());

        $sentMessage = $messagesApi->getSentMessages()[0];

        $this->assertEmpty($logger->getMessages());
        $this->assertCount(5, $sentMessage);
        $this->assertEquals('from@example.com', $sentMessage['from']);
        $this->assertEquals('some subject', $sentMessage['subject']);
        $this->assertEquals('some body', $sentMessage['html']);
        $this->assertEquals('to@example.com', $sentMessage['to']);
        $this->assertEquals('{"to@example.com":{"some_field":"value"}}', $sentMessage['recipient-variables']);
    }

    public function testSendMessageToMultipleRecipients(): void
    {
        $messagesApi = new MessagesApiMock();
        $logger = new Logger();

        $mailgunClient = new MailgunClient('some.domain', $this->createMailgunMock($messagesApi), $logger);

        $recipients = [
            new Recipient('to1@example.com', ['some_field' => 'value1']),
            new Recipient('to2@example.com', ['some_field' => 'value2']),
        ];
        $message = new Message('from@example.com', 'some subject', 'some body');

        $mailgunClient->sendMessage($recipients, $message);

        $this->assertCount(1, $messagesApi->getSentMessages());

        $sentMessage = $messagesApi->getSentMessages()[0];

        $this->assertEmpty($logger->getMessages());
        $this->assertCount(5, $sentMessage);
        $this->assertEquals('from@example.com', $sentMessage['from']);
        $this->assertEquals('some subject', $sentMessage['subject']);
        $this->assertEquals('some body', $sentMessage['html']);
        $this->assertEquals('to1@example.com,to2@example.com', $sentMessage['to']);
        $this->assertEquals('{"to1@example.com":{"some_field":"value1"},"to2@example.com":{"some_field":"value2"}}', $sentMessage['recipient-variables']);
    }

    public function testSendMessageWithThrowingException(): void
    {
        $expectedException = new Exception('Some exception message');
        $logger = new Logger();

        $mailgunClient = new MailgunClient('some.domain', $this->createMailgunMockThrowingException($expectedException), $logger);

        $recipients = [
            new Recipient('to1@example.com', ['some_field' => 'value1']),
            new Recipient('to2@example.com', ['some_field' => 'value2']),
        ];
        $message = new Message('from@example.com', 'some subject', 'some body');

        $mailgunClient->sendMessage($recipients, $message);

        $logMessages = $logger->getMessages();

        $this->assertCount(1, $logMessages);
        $this->assertEquals($expectedException->getMessage(), $logMessages[0]['context']['errorMessage']);

        foreach ($recipients as $recipient) {
            $this->assertStringContainsString($recipient->getEmail(), $logMessages[0]['context']['emails']);
        }
    }

    private function createMailgunMock(MessagesApiMock $message): Mailgun
    {
        $mailgun = $this->createMock(Mailgun::class);

        $mailgun->method('messages')->willReturn($message);

        return $mailgun;
    }

    private function createMailgunMockThrowingException(Exception $exception): Mailgun
    {
        $mailgun = $this->createMock(Mailgun::class);

        $mailgun->method('messages')->willThrowException($exception);

        return $mailgun;
    }
}
