<?php

namespace Tests\Unit\Util\Mail;

use App\Module\Mailer\SwiftMailer\Transport\FileTransport;
use Symfony\Component\Filesystem\Filesystem;
use Swift_Mailer;
use Swift_Message;
use Tests\Unit\TestCase;

/**
 * @group mailer
 */
class FileTransportTest extends TestCase
{
    private $fixture;

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
        ];
    }

    public function testSend(): void
    {
        $transport = new FileTransport(
            new Filesystem(),
            $this->getLogDirectory(),
            'test'
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

        $data = $this->loadLastEmailMessage($transport->getMailDirectory());

        $this->assertEquals(3, $countSentEmails);
        $this->assertStringContainsString('Subject: '.$this->fixture['subject'], $data);
        $this->assertStringContainsString('From: '.$this->fixture['fromAuthor'].' <'.$this->fixture['fromEmail'].'>', $data);
        $this->assertStringContainsString('To: '.$this->fixture['to'], $data);
        $this->assertStringContainsString('Cc: '.$this->fixture['cc'], $data);
        $this->assertStringContainsString('Bcc: '.$this->fixture['bcc'], $data);
        $this->assertStringContainsString('Body:'.PHP_EOL.$this->fixture['text'], $data);
    }
}
