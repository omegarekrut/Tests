<?php

namespace Tests\Functional\Domain\WeeklyLetter\Command\Handler;

use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\News\Collection\NewsCollection;
use App\Domain\WeeklyLetter\Command\Handler\SendTestWeeklyLetterHandler;
use App\Domain\WeeklyLetter\Command\SendTestWeeklyLetterCommand;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Mail\WeeklyLetterMailFactory;
use App\Domain\WeeklyLetter\Mail\WeeklyLetterRecipientsGeneratorFactory;
use App\Domain\WeeklyLetter\Service\WeeklyLetterPeriodFactory;
use App\Module\BulkMailSender\BulkMailSenderInterface;
use App\Module\BulkMailSender\Mock\BulkMailSenderMock;
use Tests\Functional\TestCase;

/**
 * @group weekly-letter
 */
class SendTestWeeklyLetterHandlerTest extends TestCase
{
    private WeeklyLetterMailFactory $weeklyLetterMailFactory;
    private BulkMailSenderInterface $bulkMailSender;
    private SendTestWeeklyLetterHandler $sendTestWeeklyLetterHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->weeklyLetterMailFactory = $this->getContainer()->get(WeeklyLetterMailFactory::class);
        $this->bulkMailSender = new BulkMailSenderMock();

        $this->sendTestWeeklyLetterHandler = new SendTestWeeklyLetterHandler(
            $this->weeklyLetterMailFactory,
            $this->bulkMailSender,
            $this->getContainer()->get(WeeklyLetterRecipientsGeneratorFactory::class)
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->sendWeeklyLetterHandler,
            $this->bulkMailSender,
            $this->weeklyLetterMailFactory
        );

        parent::tearDown();
    }

    public function testThatTestWeeklyLetterIsSentButNotSavedToDatabase(): void
    {
        $weeklyLetter = $this->createWeeklyLetter();
        $expectedSentMessage = $this->weeklyLetterMailFactory->buildWeeklyLetterMail($weeklyLetter);

        $sendTestWeeklyLetterCommand = new SendTestWeeklyLetterCommand($weeklyLetter);
        $this->sendTestWeeklyLetterHandler->handle($sendTestWeeklyLetterCommand);

        $sentMessage = $this->bulkMailSender->getSentMessage();

        $this->assertEquals($expectedSentMessage, $sentMessage);
        $this->assertNotNull($weeklyLetter->getSentAt());
        $this->assertNotNull($weeklyLetter->getRecipientsCount());
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
}
