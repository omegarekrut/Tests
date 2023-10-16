<?php

namespace Tests\Unit\Domain\Complaint\Command\Handler;

use App\Domain\Complaint\Command\Handler\SendComplaintHandler;
use App\Domain\Complaint\Command\SendComplaintCommand;
use App\Domain\Complaint\Mail\ComplaintMailFactory;
use App\Module\Mailer\MailerInterface;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\ServiceMailMailerResolverMock;
use Tests\Unit\TestCase;

class SendComplaintHandlerTest extends TestCase
{
    private SendComplaintCommand $command;
    private Swift_Message $message;
    private SendComplaintHandler $handler;
    private MailerInterface $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->generateUser();
        $this->message = new Swift_Message();
        $complaintMailFactory = $this->createMock(ComplaintMailFactory::class);

        $complaintMailFactory
            ->method('buildComplaintMail')
            ->willReturn($this->message);

        $this->mailer = new MailerMock();
        $serviceMailMailerResolver = new ServiceMailMailerResolverMock($this->mailer);

        $this->command = new SendComplaintCommand($user, '/record/uri');
        $this->command->text = 'reason text';

        $this->handler = new SendComplaintHandler($complaintMailFactory, $serviceMailMailerResolver);
    }

    public function testComplaintIsSent(): void
    {
        $this->handler->handle($this->command);

        $this->assertSame($this->message, $this->mailer->getLastSentMessage());
    }
}
