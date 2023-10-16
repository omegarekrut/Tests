<?php

namespace Tests\Unit\Domain\SpamDetection\Command;

use App\Domain\SpamDetection\Command\CheckUserForSpamCommand;
use App\Domain\SpamDetection\Command\Handler\CheckUserForSpamHandler;
use App\Domain\SpamDetection\Event\SpammerDetectedEvent;
use App\Domain\SpamDetection\SpamCheckerInterface;
use App\Domain\SpamDetection\SpamCheckingDecision;
use App\Domain\User\Entity\User;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class CheckUserForSpamHandlerTest extends TestCase
{
    public function testSpammerDetectedEventShouldBeFiredForForSpammer(): void
    {
        $eventDispatcher = new EventDispatcherMock();
        $spamChecker = $this->createMock(SpamCheckerInterface::class);
        $spamChecker
            ->method('checkUser')
            ->willReturn(new SpamCheckingDecision('spam', 'spam'));

        $checkUserForSpamHandler = new CheckUserForSpamHandler($spamChecker, $eventDispatcher);

        $user = $this->createMock(User::class);
        $command = new CheckUserForSpamCommand($user);

        $checkUserForSpamHandler->handle($command);

        $lastDispatchedEvents = $eventDispatcher->getDispatchedEvents();

        $this->assertArrayHasKey(SpammerDetectedEvent::class, $lastDispatchedEvents);
        $this->assertInstanceOf(SpammerDetectedEvent::class, current($lastDispatchedEvents[SpammerDetectedEvent::class]));
    }
}
