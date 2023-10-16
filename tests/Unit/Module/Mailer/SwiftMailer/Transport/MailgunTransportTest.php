<?php

namespace Tests\Unit\Module\Mailer\SwiftMailer\Transport;

use App\Module\Mailer\SwiftMailer\Normalizer\MessageFilesNormalizer;
use App\Module\Mailer\SwiftMailer\Transport\MailgunTransport;
use Mailgun\Api\Message;
use Mailgun\Mailgun;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Tests\Unit\TestCase;

/**
 * @group mailer
 */
class MailgunTransportTest extends TestCase
{
    private $fixture;
    private $lastSendMessageFromDomain;
    private $lastSendMessage;

    protected function setUp(): void
    {
        parent::setUp();

        $faker = $this->getFaker();
        $this->fixture = [
            'fromEmail' => $faker->email,
            'fromAuthor' => $faker->name,
            'to' => $faker->email,
            'cc' => $faker->email,
            'bcc' => $faker->email,
            'subject' => $faker->word,
            'text' => $faker->realText(),
            'contentType' => 'text/html',
        ];
    }

    public function testSend(): void
    {
        $transport = new MailgunTransport(
            $this->getMockMailgun(),
            'example.com',
            new MessageFilesNormalizer()
        );

        $mailer = new Swift_Mailer($transport);

        $message = new Swift_Message($this->fixture['subject']);
        $message
            ->setFrom([$this->fixture['fromEmail'] => $this->fixture['fromAuthor']])
            ->setTo($this->fixture['to'])
            ->setCc($this->fixture['cc'])
            ->setBcc($this->fixture['bcc'])
            ->setBody($this->fixture['text']);

        $countSentEmails = $mailer->send($message);

        $this->assertEquals(3, $countSentEmails);
        $this->assertEquals('example.com', $this->lastSendMessageFromDomain);
        $this->assertEquals([
            'to' => sprintf('<%s>', $this->fixture['to']),
            'cc' => sprintf('<%s>', $this->fixture['cc']),
            'bcc' => sprintf('<%s>', $this->fixture['bcc']),
            'from' => sprintf('%s <%s>', $this->fixture['fromAuthor'], $this->fixture['fromEmail']),
            'subject' => $this->fixture['subject'],
            'text' => $this->fixture['text'],
        ], $this->lastSendMessage);
    }

    public function testSendHtml(): void
    {
        $messageFilesNormalizer = new MessageFilesNormalizer();
        $transport = new MailgunTransport(
            $this->getMockMailgun(),
            'example.com',
            $messageFilesNormalizer
        );

        $mailer = new Swift_Mailer($transport);

        $message = new Swift_Message($this->fixture['subject']);
        $message
            ->setFrom([$this->fixture['fromEmail'] => $this->fixture['fromAuthor']])
            ->setTo($this->fixture['to'])
            ->setCc($this->fixture['cc'])
            ->setBcc($this->fixture['bcc'])
            ->setBody($this->fixture['text'])
            ->setContentType($this->fixture['contentType']);
        $message->embed($this->createMock(Swift_Image::class));

        $countSentEmails = $mailer->send($message);

        $this->assertEquals(3, $countSentEmails);
        $this->assertEquals('example.com', $this->lastSendMessageFromDomain);
        $this->assertEquals([
            'to' => sprintf('<%s>', $this->fixture['to']),
            'cc' => sprintf('<%s>', $this->fixture['cc']),
            'bcc' => sprintf('<%s>', $this->fixture['bcc']),
            'from' => sprintf('%s <%s>', $this->fixture['fromAuthor'], $this->fixture['fromEmail']),
            'subject' => $this->fixture['subject'],
            'html' => $this->fixture['text'],
            'inline' => $messageFilesNormalizer->normalize($message->getChildren()),
        ], $this->lastSendMessage);
    }

    private function getMockMailgun(): Mailgun
    {
        $messages = $this->createMock(Message::class);
        $messages->method('send')
            ->willReturnCallback(function ($domain, $message) {
                $this->lastSendMessageFromDomain = $domain;
                $this->lastSendMessage = $message;
            });

        $mailgun = $this->createMock(Mailgun::class);
        $mailgun->method('messages')
            ->willReturn($messages);

        return $mailgun;
    }
}
