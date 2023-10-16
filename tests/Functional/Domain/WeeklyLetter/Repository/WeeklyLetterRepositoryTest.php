<?php

namespace Tests\Functional\Domain\WeeklyLetter\Repository;

use App\Domain\WeeklyLetter\Entity\WeeklyLetter;
use App\Domain\WeeklyLetter\Repository\WeeklyLetterRepository;
use App\Domain\WeeklyLetter\Service\WeeklyLetterPeriodFactory;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterCurrent;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterBefore;
use Tests\Functional\TestCase;

/**
 * @group weekly-letter
 */
class WeeklyLetterRepositoryTest extends TestCase
{
    /** @var WeeklyLetterRepository */
    private $weeklyLetterRepository;
    /** @var WeeklyLetter */
    private $expectedLastWeeklyLetter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->weeklyLetterRepository = $this->getContainer()->get(WeeklyLetterRepository::class);

        $referenceRepository = $this->loadFixtures([
            LoadWeeklyLetterBefore::class,
            LoadWeeklyLetterCurrent::class,
        ])->getReferenceRepository();

        $this->expectedLastWeeklyLetter = $referenceRepository->getReference(LoadWeeklyLetterCurrent::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->expectedLastWeeklyLetter,
            $this->weeklyLetterRepository
        );

        parent::tearDown();
    }

    public function testFindAllOrderByNumberDesc(): void
    {
        $allWeeklyLetters = $this->weeklyLetterRepository->findAllOrderByNumberDesc();

        $this->assertCount(2, $allWeeklyLetters);

        $this->assertTrue($allWeeklyLetters[0]->getNumber() > $allWeeklyLetters[1]->getNumber());
    }

    public function testFindLastWeeklyLetter(): void
    {
        $lastWeeklyLetter = $this->weeklyLetterRepository->findLastWeeklyLetter();

        $this->assertEquals($this->expectedLastWeeklyLetter, $lastWeeklyLetter);
    }

    public function testFindWeeklyLetterForPeriod(): void
    {
        $currentWeekPeriod = WeeklyLetterPeriodFactory::createCurrentWeeklyLetterPeriod();
        $previousWeekPeriod = WeeklyLetterPeriodFactory::createPreviousWeeklyLetterPeriod();
        $weekPeriod2WeeksAgo = WeeklyLetterPeriodFactory::createPeriodFromTuesdayToMondayWeeksAgo(2);

        $lastWeeklyLetter = $this->weeklyLetterRepository->findWeeklyLetterForPeriod($currentWeekPeriod);
        $previousWeeklyLetter = $this->weeklyLetterRepository->findWeeklyLetterForPeriod($previousWeekPeriod);
        $weeklyLetter2WeeksAgo = $this->weeklyLetterRepository->findWeeklyLetterForPeriod($weekPeriod2WeeksAgo);

        $this->assertNotNull($lastWeeklyLetter);
        $this->assertNotNull($previousWeeklyLetter);
        $this->assertNull($weeklyLetter2WeeksAgo);
    }
}
