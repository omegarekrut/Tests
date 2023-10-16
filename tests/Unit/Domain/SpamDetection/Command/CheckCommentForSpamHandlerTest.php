<?php

namespace Tests\Unit\Domain\SpamDetection\Command;

use App\Domain\Comment\Entity\Comment;
use App\Domain\SpamDetection\Command\CheckCommentForSpamCommand;
use App\Domain\SpamDetection\Command\Handler\CheckCommentForSpamHandler;
use App\Domain\SpamDetection\Event\SpammerDetectedEvent;
use App\Domain\SpamDetection\SpamCheckerInterface;
use App\Domain\SpamDetection\SpamCheckingDecision;
use App\Domain\User\Entity\User;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

/**
 * @group spam-detection
 */
class CheckCommentForSpamHandlerTest extends TestCase
{
    public function testSpammerDetectedEventShouldBeFiredForForSpamComment(): void
    {
        $eventDispatcher = new EventDispatcherMock();
        $spamChecker = $this->createMock(SpamCheckerInterface::class);
        $spamChecker
            ->method('checkComment')
            ->willReturn(new SpamCheckingDecision('spam', 'spam'));

        $checkCommentForSpamHandler = new CheckCommentForSpamHandler($spamChecker, $eventDispatcher);

        $commentAuthor = $this->createMock(User::class);
        $comment = $this->createMock(Comment::class);
        $comment
            ->method('getAuthor')
            ->willReturn($commentAuthor);

        $command = new CheckCommentForSpamCommand($comment);
        $checkCommentForSpamHandler->handle($command);

        $lastDispatchedEvents = $eventDispatcher->getDispatchedEvents();

        $this->assertArrayHasKey(SpammerDetectedEvent::class, $lastDispatchedEvents);
        $this->assertInstanceOf(SpammerDetectedEvent::class, current($lastDispatchedEvents[SpammerDetectedEvent::class]));
    }
}
