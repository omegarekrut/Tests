<?php

namespace Tests\Unit\Module\Mail\DisposableEmailChecker;

use App\Module\Mail\DisposableEmailChecker\DisposableEmailCheckerInterface;
use App\Module\Mail\DisposableEmailChecker\LoggableDisposableEmailChecker;
use App\Module\Mail\Exception\EmailCheckerClientException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Tests\Unit\TestCase;

class LoggableDisposableEmailCheckerTest extends TestCase
{
    private DisposableEmailCheckerInterface $disposableEmailChecker;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disposableEmailChecker = $this->createMock(DisposableEmailCheckerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testCheckWriteLogsIfCheckerThrowClientException(): void
    {
        $clientException = new EmailCheckerClientException();

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with((string) $clientException);

        $disposableEmail = 'disposable@email.com';

        $this->expectException(EmailCheckerClientException::class);

        $this->disposableEmailChecker
            ->method('isDisposableEmail')
            ->with($disposableEmail)
            ->willThrowException($clientException);

        $loggableDisposableEmailChecker = new LoggableDisposableEmailChecker($this->logger, $this->disposableEmailChecker);

        $loggableDisposableEmailChecker->isDisposableEmail($disposableEmail);
    }

    public function testCheckDoesntWriteLogsIfCheckerThrowNotClientException(): void
    {
        $this->logger
            ->expects($this->never())
            ->method('warning');

        $notFullEmail = 'disposable@e';

        $this->expectException(InvalidArgumentException::class);

        $this->disposableEmailChecker
            ->method('isDisposableEmail')
            ->with($notFullEmail)
            ->willThrowException(new InvalidArgumentException());

        $loggableDisposableEmailChecker = new LoggableDisposableEmailChecker($this->logger, $this->disposableEmailChecker);

        $loggableDisposableEmailChecker->isDisposableEmail($notFullEmail);
    }
}
