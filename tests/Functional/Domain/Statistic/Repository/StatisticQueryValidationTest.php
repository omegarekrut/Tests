<?php

namespace Tests\Functional\Domain\Statistic\Repository;

use App\Domain\Statistic\Entity\StatisticReport\ValueObject\GroupingInterval;
use App\Domain\Statistic\Repository\UsersActivityStatisticQuery;
use Carbon\Carbon;
use Tests\Functional\ValidationTestCase;

/**
 * @group statistic
 */
class StatisticQueryValidationTest extends ValidationTestCase
{
    /** @var UsersActivityStatisticQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = new UsersActivityStatisticQuery();
    }

    protected function tearDown(): void
    {
        unset($this->query);

        parent::tearDown();
    }

    public function testAllFieldMustBeNotBlank(): void
    {
        $this->query->periodFrom = null;
        $this->query->periodTo = null;
        $this->query->group = null;

        $this->getValidator()->validate($this->query);

        foreach (['periodFrom', 'periodTo', 'group'] as $propertyPath) {
            $this->assertFieldInvalid($propertyPath, 'Это поле обязательно для заполнения.');
        }
    }

    public function testPeriodDatesMustBeDateTime(): void
    {
        $this->query->periodFrom = '12:00 2020-01-01';
        $this->query->periodTo = '15:00 2020-01-02';

        $this->getValidator()->validate($this->query);

        $expectedViolationMessage = 'Тип значения должен быть \DateTimeInterface.';

        $this->assertFieldInvalid('periodFrom', $expectedViolationMessage);
        $this->assertFieldInvalid('periodTo', $expectedViolationMessage);
    }

    public function testPeriodCannotBeStartedInFuture(): void
    {
        $this->query->periodFrom = Carbon::now()->addDay();

        $this->getValidator()->validate($this->query);

        $this->assertFieldInvalid('periodFrom', 'Период статистики должен начинаться в прошлом.');
    }

    public function testPeriodCannotBeEndedEarlierThenStarted(): void
    {
        $this->query->periodFrom = Carbon::now();
        $this->query->periodTo = Carbon::now()->subDay();

        $this->getValidator()->validate($this->query);

        $this->assertFieldInvalid('periodTo', 'Дата окончания периода статистики должна быть позже даты ее начала.');
    }

    public function testGroupIntervalMustBeValidInterval(): void
    {
        $this->query->group = 'invalid group interval value';

        $this->getValidator()->validate($this->query);

        $this->assertFieldInvalid('group', 'Указан некорректный интервал статистики.');
    }

    public function testValidationShouldBePassedForCorrectDefaultCreatedQuery(): void
    {
        $this->getValidator()->validate($this->query);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationShouldBePassedForCorrectFilledQuery(): void
    {
        $this->query->periodFrom = Carbon::now()->subYear();
        $this->query->periodTo = Carbon::now();
        $this->query->group = (string) GroupingInterval::byMonths();

        $this->getValidator()->validate($this->query);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
