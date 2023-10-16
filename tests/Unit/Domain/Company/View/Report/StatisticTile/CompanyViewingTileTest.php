<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticTile;

use App\Domain\Company\View\Report\StatisticTile\CompanyViewingTile;
use App\Domain\Company\View\Report\StatisticTile\StatisticsChangeType;
use Tests\Unit\TestCase;

class CompanyViewingTileTest extends TestCase
{
    public function testGetGroupName(): void
    {
        $this->assertEquals('Просмотры', CompanyViewingTile::getGroupName());
    }

    public function testGetGroupDescription(): void
    {
        $this->assertEquals(
            'Столько раз пользователи просматривали профиль вашей компании за последние 30 дней.',
            CompanyViewingTile::getGroupDescription()
        );
    }

    public function testGetDataForView(): void
    {
        $expectedCount = 42;
        $expectedChangeType = StatisticsChangeType::positiveChangeType();

        $viewMonthGroupStatistic = new CompanyViewingTile($expectedCount, $expectedChangeType);

        $this->assertEquals($expectedCount, $viewMonthGroupStatistic->getCount());
        $this->assertTrue($expectedChangeType->equals($viewMonthGroupStatistic->getMonthChangeType()));
    }
}
