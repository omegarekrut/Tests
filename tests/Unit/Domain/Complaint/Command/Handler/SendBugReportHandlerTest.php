<?php

namespace Tests\Unit\Domain\Complaint\Command\Handler;

use App\Domain\Complaint\Command\Handler\SendBugReportHandler;
use App\Domain\Complaint\Command\SendBugReportCommand;
use App\Domain\Complaint\Mail\BugReportMailFactory;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\ServiceMailMailerResolverMock;
use Tests\Unit\TestCase;

class SendBugReportHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $complainant = $this->generateUser();

        $mailer = new MailerMock();
        $serviceMailMailerResolver = new ServiceMailMailerResolverMock($mailer);
        $complaintMailFactory = $this->createMock(BugReportMailFactory::class);

        $expectedMessage = new Swift_Message();
        $complaintMailFactory
            ->method('buildMail')
            ->willReturn($expectedMessage);

        $command = new SendBugReportCommand($complainant, '/companies/zao-radio');
        $command->text = 'bug text';

        $handler = new SendBugReportHandler($serviceMailMailerResolver, $complaintMailFactory);

        $handler->handle($command);

        $this->assertSame($expectedMessage, $mailer->getLastSentMessage());
    }
}
