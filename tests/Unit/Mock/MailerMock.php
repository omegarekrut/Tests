<?php
namespace Tests\Unit\Mock;

use App\Module\Mailer\MailerInterface;
use Swift_Message;

class MailerMock implements MailerInterface
{
    private $messagesSent = [];

    public function send(Swift_Message $message): void
    {
        $this->messagesSent[] = $message;
    }

    public function getLastSentMessage(): ?Swift_Message
    {
        return count($this->messagesSent) ? $this->messagesSent[count($this->messagesSent)-1] : null;
    }
}
