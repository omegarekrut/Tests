<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\EventSubscriber\NewsletterEventsSubscriber;
use App\Domain\WeeklyLetter\Command\SendTestWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Command\SendWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Event\TestWeeklyLetterCreatedEvent;
use App\Domain\WeeklyLetter\Event\WeeklyLetterCreatedEvent;
use League\Tactician\CommandBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterCurrent;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

class NewsletterEventsSubscriberTest extends TestCase
{
    private CommandBus $commandBusMock;
    private EventDispatcherInterface $eventDispatcher;
    private WeeklyLetter $weeklyLetter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBusMock = new CommandBusMock();
        $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $this->eventDispatcher->addSubscriber(new NewsletterEventsSubscriber($this->commandBusMock));

        $referenceRepository = $this->loadFixtures([
            LoadWeeklyLetterCurrent::class,
        ])->getReferenceRepository();

        $this->weeklyLetter = $referenceRepository->getReference(LoadWeeklyLetterCurrent::REFERENCE_NAME);
    }

    public function testSendWeeklyLetterAfterCreateWeeklyLetter(): void
    {
        $this->eventDispatcher->dispatch(new WeeklyLetterCreatedEvent($this->weeklyLetter));

        $this->assertTrue($this->commandBusMock->isHandled(SendWeeklyLetterCommand::class));
    }

    public function testSendTestWeeklyLetterAfterCreateTestWeeklyLetter(): void
    {
        $this->eventDispatcher->dispatch(new TestWeeklyLetterCreatedEvent($this->weeklyLetter));

        $this->assertTrue($this->commandBusMock->isHandled(SendTestWeeklyLetterCommand::class));
    }
}
