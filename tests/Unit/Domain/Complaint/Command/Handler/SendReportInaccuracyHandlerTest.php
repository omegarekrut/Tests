<?php

namespace Tests\Unit\Domain\Complaint\Command\Handler;

use App\Domain\Complaint\Command\Handler\SendReportInaccuracyHandler;
use App\Domain\Complaint\Command\SendReportInaccuracyCommand;
use App\Domain\Complaint\Mail\ReportInaccuracyMailFactory;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\ServiceMailMailerResolverMock;
use Tests\Unit\TestCase;

class SendReportInaccuracyHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $complainant = $this->generateUser();
        $message = new Swift_Message();
        $mailer = new MailerMock();
        $serviceMailMailerResolver = new ServiceMailMailerResolverMock($mailer);
        $complaintMailFactory = $this->createMock(ReportInaccuracyMailFactory::class);

        $complaintMailFactory
            ->method('buildMail')
            ->willReturn($message);

        $command = new SendReportInaccuracyCommand($complainant, '/companies/zao-radio');
        $command->setText('reason text');

        $handler = new SendReportInaccuracyHandler($serviceMailMailerResolver, $complaintMailFactory);

        $handler->handle($command);

        $this->assertSame($message, $mailer->getLastSentMessage());
    }
}
