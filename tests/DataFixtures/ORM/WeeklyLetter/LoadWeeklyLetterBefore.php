<?php

namespace Tests\DataFixtures\ORM\WeeklyLetter;

use App\Domain\MailingBlockAd\Entity\MailingBlockAd;
use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\News\Collection\NewsCollection;
use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Service\WeeklyLetterNumberGenerator;
use App\Domain\WeeklyLetter\Service\WeeklyLetterPeriodFactory;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\MailingBlockAd\LoadMailingBlockAd;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadArticleOfCompanyWithPremiumSubscription;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;

class LoadWeeklyLetterBefore extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'previous-weekly-letter';
    private const DEFAULT_WEEKLY_LETTER_FORUM_TOPICS = [4, 8, 15, 16, 23, 42];
    private const RECIPIENTS_COUNT = 80;
    private const RECORDS_FIXTURES_CLASSES_NAMES = [
        LoadNews::class,
        LoadTackleReviews::class,
        LoadArticles::class,
        LoadNumberedTidings::class,
        LoadGallery::class,
        LoadVideos::class,
    ];

    public function load(ObjectManager $manager): void
    {
        $weeklyLetter = $this->createWeeklyLetter();
        $weeklyLetter->setSentAt(
            Carbon::instance($weeklyLetter->getPeriodTo())->addDay()
        );
        $weeklyLetter->setRecipientsCount(self::RECIPIENTS_COUNT);

        $manager->persist($weeklyLetter);

        $this->addReference(self::REFERENCE_NAME, $weeklyLetter);

        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadNews::class,
            LoadTackleReviews::class,
            LoadArticles::class,
            LoadNumberedTidings::class,
            LoadGallery::class,
            LoadVideos::class,
            LoadMailingBlockAd::class,
            LoadArticleOfCompanyWithPremiumSubscription::class,
        ];
    }

    private function createWeeklyLetter(): WeeklyLetter
    {
        $weeklyLetterNumber = WeeklyLetterNumberGenerator::DEFAULT_WEEKLY_LETTER_START_NUMBER;
        $period = WeeklyLetterPeriodFactory::createPreviousWeeklyLetterPeriod();
        $records = $this->getDifferentTypesOfRecords();
        $news = $this->getNews();
        $companyArticles = $this->getCompanyArticles();
        $topics = self::DEFAULT_WEEKLY_LETTER_FORUM_TOPICS;

        $mailingBlockAd = $this->getReference(LoadMailingBlockAd::REFERENCE_NAME);
        assert($mailingBlockAd instanceof  MailingBlockAd);

        return new WeeklyLetter($weeklyLetterNumber, $period, $records, $news, $companyArticles, $topics, $mailingBlockAd);
    }

    private function getDifferentTypesOfRecords(): RecordCollection
    {
        $records = [];

        foreach (self::RECORDS_FIXTURES_CLASSES_NAMES as $fixtureClass) {
            $records[] = $this->getReference($fixtureClass::getRandReferenceName());
        }

        return new RecordCollection($records);
    }

    private function getNews(): NewsCollection
    {
        return new NewsCollection([
            $this->getReference(LoadNews::getRandReferenceName()),
        ]);
    }

    private function getCompanyArticles(): CompanyArticleCollection
    {
        return new CompanyArticleCollection([
            $this->getReference(LoadArticleOfCompanyWithPremiumSubscription::getReferenceName()),
        ]);
    }
}
