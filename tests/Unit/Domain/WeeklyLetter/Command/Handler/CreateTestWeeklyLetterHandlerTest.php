<?php

namespace Tests\Unit\Domain\WeeklyLetter\Command\Handler;

use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\News\Collection\NewsCollection;
use App\Domain\WeeklyLetter\Command\CreateTestWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Command\Handler\CreateTestWeeklyLetterHandler;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Event\TestWeeklyLetterCreatedEvent;
use App\Domain\WeeklyLetter\Service\WeeklyLetterFactory;
use App\Domain\WeeklyLetter\Service\WeeklyLetterPeriodFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

/**
 * @group weekly-letter
 */
class CreateTestWeeklyLetterHandlerTest extends TestCase
{
    private EventDispatcherInterface $eventDispatcher;
    private CreateTestWeeklyLetterHandler $createTestWeeklyLetterHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = new EventDispatcherMock();

        $this->createTestWeeklyLetterHandler = new CreateTestWeeklyLetterHandler(
            $this->createWeeklyLetterFactoryMock(),
            $this->eventDispatcher
        );
    }

    private function createWeeklyLetterFactoryMock(): WeeklyLetterFactory
    {
        $weeklyLetterFactory = $this->createMock(WeeklyLetterFactory::class);
        $weeklyLetterFactory->method('createWeeklyLetterForPeriod')
            ->willReturn($this->createWeeklyLetter());

        return $weeklyLetterFactory;
    }

    private function createWeeklyLetter(): WeeklyLetter
    {
        return new WeeklyLetter(
            11,
            WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod(),
            new RecordCollection([]),
            new NewsCollection([]),
            new CompanyArticleCollection([]),
            [],
            null
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->createTestWeeklyLetterHandler,
            $this->eventDispatcher
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $expectedWeeklyLetter = $this->createWeeklyLetter();

        $createTestWeeklyLetterCommand = new CreateTestWeeklyLetterCommand(
            WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod()
        );
        $this->createTestWeeklyLetterHandler->handle($createTestWeeklyLetterCommand);

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        $testWeeklyLetterCreatedEvent = array_pop($dispatchedEvents)[0];
        assert($testWeeklyLetterCreatedEvent instanceof TestWeeklyLetterCreatedEvent);

        $eventWeeklyLetter = $testWeeklyLetterCreatedEvent->getWeeklyLetter();
        $this->assertEquals($expectedWeeklyLetter->getNumber(), $eventWeeklyLetter->getNumber());
        $this->assertEquals($expectedWeeklyLetter->getPeriodFrom(), $eventWeeklyLetter->getPeriodFrom());
        $this->assertEquals($expectedWeeklyLetter->getPeriodTo(), $eventWeeklyLetter->getPeriodTo());
    }
}
