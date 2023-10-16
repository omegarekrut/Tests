<?php

namespace Tests\Unit\Domain\WeeklyLetter\Mail;

use App\Domain\MailingBlockAd\Repository\MailingBlockAdRepository;
use App\Domain\MailingBlockAd\View\MailingBlockAdViewFactory;
use App\Domain\Record\Common\View\RecordViewCollection;
use App\Domain\Record\Common\View\RecordViewFactory;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Mail\WeeklyLetterMailFactory;
use App\Module\WeeklyLetterForumTopicsProvider\WeeklyLetterForumTopicsProvider;
use Carbon\Carbon;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

/**
 * @group weekly-letter
 */
class WeeklyLetterMailFactoryTest extends TestCase
{
    use TwigEnvironmentTrait;

    public function testBuildWeeklyLetterMail(): void
    {
        $weeklyLetterNumber = 10;
        $weeklyLetter = $this->mockWeeklyLetter($weeklyLetterNumber);

        $factory = $this->createWeeklyLetterMailFactory($weeklyLetter);

        $weeklyLetterMail = $factory->buildWeeklyLetterMail($weeklyLetter);

        $this->assertEquals(
            'FishingSib <subscribe@fishingsib.ru>',
            $weeklyLetterMail->getFrom()
        );
        $this->assertEquals(
            sprintf('Рассылка №%d. Самое интересное за неделю', $weeklyLetterNumber),
            $weeklyLetterMail->getSubject()
        );
        $this->assertEquals('weekly_letter_mail_body', $weeklyLetterMail->getBody());
    }

    private function mockWeeklyLetter(int $weeklyLetterNumber): WeeklyLetter
    {
        $weeklyLetter = $this->createMock(WeeklyLetter::class);

        $weeklyLetter->method('getNumber')
            ->willReturn($weeklyLetterNumber);
        $weeklyLetter->method('getSubject')
            ->willReturn(sprintf('Рассылка №%d. Самое интересное за неделю', $weeklyLetterNumber));
        $weeklyLetter->method('getPeriodFrom')
            ->willReturn(Carbon::today()->subWeek());
        $weeklyLetter->method('getPeriodTo')
            ->willReturn(Carbon::today()->endOfDay());

        return $weeklyLetter;
    }

    private function createWeeklyLetterMailFactory(WeeklyLetter $weeklyLetter): WeeklyLetterMailFactory
    {
        $expectedRecordViews = new RecordViewCollection([]);

        $recordViewFactory = $this->createConfiguredMock(RecordViewFactory::class, [
            'createByCollection' => $expectedRecordViews,
        ]);

        $mailingBlockAdRepository = $this->createMock(MailingBlockAdRepository::class);
        $mailingBlockAdViewFactory = $this->createMock(MailingBlockAdViewFactory::class);

        return new WeeklyLetterMailFactory(
            'subscribe@fishingsib.ru',
            'FishingSib',
            $this->mockTwigEnvironment('mail/mailing/weekly_letter.html.inky.twig', [
                'weeklyLetterNumber' => $weeklyLetter->getNumber(),
                'periodFrom' => $weeklyLetter->getPeriodFrom(),
                'periodTo' => $weeklyLetter->getPeriodTo(),
                'recordViews' => $expectedRecordViews,
                'newsViews' => $expectedRecordViews,
                'companyArticlesViews' => $expectedRecordViews,
                'forumTopics' => [],
                'googleAnalyticsSourceImage' => '%recipient.googleAnalyticsSourceImage%',
                'unsubscribeLink' => '%recipient.unsubscribeLink%',
                'mailingBlockAd' => null,
            ], 'weekly_letter_mail_body'),
            $recordViewFactory,
            $this->createMock(WeeklyLetterForumTopicsProvider::class),
            $mailingBlockAdViewFactory,
            $mailingBlockAdRepository,
        );
    }
}
