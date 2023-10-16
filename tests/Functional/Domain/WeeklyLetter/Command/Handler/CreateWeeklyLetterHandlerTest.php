<?php

namespace Tests\Functional\Domain\WeeklyLetter\Command\Handler;

use App\Domain\BusinessSubscription\Entity\ValueObject\TariffsType;
use App\Domain\MailingBlockAd\Entity\MailingBlockAd;
use App\Domain\MailingBlockAd\Repository\MailingBlockAdRepository;
use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\Record\CompanyArticle\Repository\CompanyArticleRepository;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\News\Collection\NewsCollection;
use App\Domain\Record\News\Repository\NewsRepository;
use App\Domain\WeeklyLetter\Command\CreateWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Command\Handler\CreateWeeklyLetterHandler;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Event\WeeklyLetterCreatedEvent;
use App\Domain\WeeklyLetter\Repository\WeeklyLetterRepository;
use App\Domain\WeeklyLetter\Service\WeeklyLetterFactory;
use App\Domain\WeeklyLetter\Service\WeeklyLetterNumberGenerator;
use App\Domain\WeeklyLetter\Service\WeeklyLetterPeriodFactory;
use Carbon\Carbon;
use DatePeriod;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterBefore;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\EventDispatcherMock;

/**
 * @group weekly-letter
 */
class CreateWeeklyLetterHandlerTest extends TestCase
{
    private const TEST_MOST_ACTIVE_FORUM_TOPICS = [4, 8, 15, 16, 23, 42];

    private WeeklyLetter $previousWeeklyLetter;
    private WeeklyLetterRepository $weeklyLetterRepository;
    private WeeklyLetterFactory $weeklyLetterFactory;
    private EventDispatcherInterface  $eventDispatcher;
    private CreateWeeklyLetterHandler $createWeeklyLetterHandler;
    private MailingBlockAdRepository $mailingBlockAdRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // In order to offset "created_at" of records
        Carbon::setTestNow(Carbon::today()->previous(WeeklyLetterPeriodFactory::DEFAULT_WEEKLY_LETTER_PERIOD_LAST_DAY));

        $referenceRepository = $this->loadFixtures([
            LoadWeeklyLetterBefore::class,
        ])->getReferenceRepository();

        $this->previousWeeklyLetter = $referenceRepository->getReference(LoadWeeklyLetterBefore::REFERENCE_NAME);
        $this->weeklyLetterRepository = $this->getContainer()->get(WeeklyLetterRepository::class);
        $this->weeklyLetterFactory = $this->getContainer()->get(WeeklyLetterFactory::class);
        $this->eventDispatcher = new EventDispatcherMock();

        $this->createWeeklyLetterHandler = $this->createCreateWeeklyLetterHandler();

        $this->mailingBlockAdRepository = $this->getContainer()->get(MailingBlockAdRepository::class);

        Carbon::setTestNow();
    }

    private function createCreateWeeklyLetterHandler(): CreateWeeklyLetterHandler
    {
        return new CreateWeeklyLetterHandler(
            $this->weeklyLetterRepository,
            $this->weeklyLetterFactory,
            $this->eventDispatcher
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->createWeeklyLetterHandler,
            $this->eventDispatcher,
            $this->weeklyLetterFactory,
            $this->weeklyLetterRepository,
            $this->previousWeeklyLetter
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $expectedWeeklyLetter = $this->createExpectedWeeklyLetter();

        $createWeeklyLetterCommand = new CreateWeeklyLetterCommand(
            WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod()
        );
        $this->createWeeklyLetterHandler->handle($createWeeklyLetterCommand);

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();

        $weeklyLetterCreatedEvent = array_pop($dispatchedEvents)[0];
        assert($weeklyLetterCreatedEvent instanceof WeeklyLetterCreatedEvent);

        $eventWeeklyLetter = $weeklyLetterCreatedEvent->getWeeklyLetter();

        $newWeeklyLetter = $this->weeklyLetterRepository->findLastWeeklyLetter();

        $this->assertNotEquals($this->previousWeeklyLetter->getId(), $newWeeklyLetter->getId());

        $this->assertEquals($expectedWeeklyLetter->getNumber(), $newWeeklyLetter->getNumber());
        $this->assertEquals($expectedWeeklyLetter->getPeriodFrom(), $newWeeklyLetter->getPeriodFrom());
        $this->assertEquals($expectedWeeklyLetter->getPeriodTo(), $newWeeklyLetter->getPeriodTo());
        $this->assertEquals($expectedWeeklyLetter->getRecords(), $newWeeklyLetter->getRecords());
        $this->assertEquals($expectedWeeklyLetter->getNews(), $newWeeklyLetter->getNews());
        $this->assertEquals($expectedWeeklyLetter->getCompanyArticles(), $newWeeklyLetter->getCompanyArticles());
        $this->assertEquals($expectedWeeklyLetter->getForumTopics(), $newWeeklyLetter->getForumTopics());

        $this->assertEquals($newWeeklyLetter->getId(), $eventWeeklyLetter->getId());
        $this->assertEquals($newWeeklyLetter->getMailingBlockAd(), $eventWeeklyLetter->getMailingBlockAd());
    }

    private function createExpectedWeeklyLetter(): WeeklyLetter
    {
        $weeklyLetterPeriod = WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod();
        $weeklyLetterNumberGenerator = new WeeklyLetterNumberGenerator($this->weeklyLetterRepository);
        $mailingBlockAd = $this->getMailingBlockAd();

        return new WeeklyLetter(
            $weeklyLetterNumberGenerator->getNewWeeklyLetterNumber(),
            WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod(),
            $this->getExpectedWeeklyLetterRecords($weeklyLetterPeriod),
            $this->getExpectedWeeklyLetterNews($weeklyLetterPeriod),
            $this->getExpectedWeeklyLetterCompanyArticles($weeklyLetterPeriod),
            self::TEST_MOST_ACTIVE_FORUM_TOPICS,
            $mailingBlockAd
        );
    }

    private function getExpectedWeeklyLetterRecords(DatePeriod $expectedWeeklyLetterPeriod): RecordCollection
    {
        $recordRepository = $this->getContainer()->get(RecordRepository::class);

        return $recordRepository->findMostRatedForPeriodWithoutRecordTypes(
            $expectedWeeklyLetterPeriod,
            WeeklyLetterFactory::DEFAULT_EXCLUDED_TYPES_OF_RECORDS,
            WeeklyLetterFactory::DEFAULT_RECORDS_LIMIT
        );
    }

    private function getExpectedWeeklyLetterNews(DatePeriod $expectedWeeklyLetterPeriod): NewsCollection
    {
        $newsRepository = $this->getContainer()->get(NewsRepository::class);

        return $newsRepository->findAllForPeriod($expectedWeeklyLetterPeriod);
    }

    private function getExpectedWeeklyLetterCompanyArticles(DatePeriod $expectedWeeklyLetterPeriod): CompanyArticleCollection
    {
        $companyArticleRepository = $this->getContainer()->get(CompanyArticleRepository::class);

        return $companyArticleRepository->findForCompanyWithSubscriptionByLimit(
            $expectedWeeklyLetterPeriod,
            TariffsType::premium(),
            WeeklyLetterFactory::DEFAULT_COMPANY_ARTICLES
        );
    }

    private function getMailingBlockAd(): MailingBlockAd
    {
        return $this->mailingBlockAdRepository->findByDate(Carbon::today());
    }

    public function testWeeklyLetterIsNotCreatedIfAlreadyExist(): void
    {
        $createWeeklyLetterCommand = new CreateWeeklyLetterCommand(
            WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod()
        );

        $this->createWeeklyLetterHandler->handle($createWeeklyLetterCommand);
        $lastWeeklyLetter = $this->weeklyLetterRepository->findLastWeeklyLetter();

        $this->createWeeklyLetterHandler->handle($createWeeklyLetterCommand);
        $newWeeklyLetter = $this->weeklyLetterRepository->findLastWeeklyLetter();

        $this->assertEquals($lastWeeklyLetter, $newWeeklyLetter);
    }
}
