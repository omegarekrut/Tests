<?php

namespace Tests\Unit\Domain\WeeklyLetter\Entity;

use App\Domain\MailingBlockAd\Entity\MailingBlockAd;
use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\News\Collection\NewsCollection;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Service\WeeklyLetterPeriodFactory;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;
use InvalidArgumentException;
use Exception;

/**
 * @group weekly-letter
 */
class WeeklyLetterTest extends TestCase
{
    public function testAssertDateMailingBlockAdIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailing block ad date is incorrect.');

        $mailingBlockAd = new MailingBlockAd(
            Uuid::uuid4(),
            'title',
            'data',
            new Image('test.jpg'),
            Carbon::now()->subMonth(),
            Carbon::now()->subday(),
        );

        new WeeklyLetter(
            11,
            WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod(),
            new RecordCollection([]),
            new NewsCollection([]),
            new CompanyArticleCollection([]),
            [],
            $mailingBlockAd
        );
    }

    public function testAssertDateMailingBlockAdIsValid(): void
    {
        $actualException = null;

        try {
            $mailingBlockAd = new MailingBlockAd(
                Uuid::uuid4(),
                'title',
                'data',
                new Image('test.jpg'),
                Carbon::now()->subMonth(),
                Carbon::now()->addWeek(),
            );

            new WeeklyLetter(
                11,
                WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod(),
                new RecordCollection([]),
                new NewsCollection([]),
                new CompanyArticleCollection([]),
                [],
                $mailingBlockAd
            );
        } catch (Exception $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }

    public function testNullMailingBlockInWeeklyLeater(): void
    {
        $actualException = null;

        try {
            $mailingBlockAd = null;

            new WeeklyLetter(
                11,
                WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod(),
                new RecordCollection([]),
                new NewsCollection([]),
                new CompanyArticleCollection([]),
                [],
                $mailingBlockAd
            );
        } catch (Exception $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }
}
