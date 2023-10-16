<?php

namespace Tests\Unit\Mock\BulkMailSender\Mailgun;

use App\Module\BulkMailSender\Mailgun\MailgunClientInterface;
use App\Module\BulkMailSender\TransferObject\Message;
use App\Module\BulkMailSender\TransferObject\Recipient;

class MailgunClientMock implements MailgunClientInterface
{
    private $sentBatchesOfMessages = [];

    /**
     * @param Recipient[] $recipients
     */
    public function sendMessage(array $recipients, Message $message): void
    {
        $this->sentBatchesOfMessages[] = [
            'batchOfRecipients' => $recipients,
            'message' => $message,
        ];
    }

    public function getSentBatchesOfMessages(): array
    {
        return $this->sentBatchesOfMessages;
    }

    public function getSentBatchOfRecipients(int $index): array
    {
        return $this->sentBatchesOfMessages[$index]['batchOfRecipients'];
    }

    public function getSentMessage(int $index): Message
    {
        return $this->sentBatchesOfMessages[$index]['message'];
    }
}
