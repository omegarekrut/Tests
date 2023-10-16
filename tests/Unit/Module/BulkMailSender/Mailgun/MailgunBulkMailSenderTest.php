<?php

namespace Tests\Unit\Module\BulkMailSender\Mailgun;

use App\Module\BulkMailSender\Mailgun\MailgunBulkMailSender;
use App\Module\BulkMailSender\TransferObject\Message;
use App\Module\BulkMailSender\TransferObject\Recipient;
use Generator;
use Tests\Unit\Mock\BulkMailSender\Mailgun\MailgunClientMock;
use Tests\Unit\TestCase;

class MailgunBulkMailSenderTest extends TestCase
{
    public function testSend(): void
    {
        $mailgunClient = new MailgunClientMock();

        $bulkMailSender = new MailgunBulkMailSender($mailgunClient);

        $message = new Message('from@email.com', 'some subject', 'some body');

        $recipientsCount = $bulkMailSender->send($this->createRecipientsGenerator(500), $message);

        $this->assertEquals(500, $recipientsCount);
        $this->assertCount(1, $mailgunClient->getSentBatchesOfMessages());
        $this->assertEquals($message, $mailgunClient->getSentMessage(0));
    }

    public function testSendMultipleBatch(): void
    {
        $mailgunClient = new MailgunClientMock();

        $bulkMailSender = new MailgunBulkMailSender($mailgunClient);

        $message = new Message('from@email.com', 'some subject', 'some body');

        $recipientsCount = $bulkMailSender->send($this->createRecipientsGenerator(1500), $message);

        $this->assertEquals(1500, $recipientsCount);
        $this->assertCount(2, $mailgunClient->getSentBatchesOfMessages());

        $this->assertCount(1000, $mailgunClient->getSentBatchOfRecipients(0));
        $this->assertEquals($message, $mailgunClient->getSentMessage(0));

        $this->assertCount(500, $mailgunClient->getSentBatchOfRecipients(1));
        $this->assertEquals($message, $mailgunClient->getSentMessage(1));
    }

    private function createRecipientsGenerator(int $recipientsCount): Generator
    {
        $recipient = new Recipient('to@email.com', [
            'some' => 'field',
        ]);

        for ($i = 0; $i < $recipientsCount; $i++) {
            yield $recipient;
        }
    }
}
