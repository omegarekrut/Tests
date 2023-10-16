<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticTile;

use App\Domain\Company\View\Report\StatisticTile\StatisticsChangeType;
use App\Domain\Company\View\Report\StatisticTile\TransitionToSocialNetworksTile;
use Tests\Unit\TestCase;

class TransitionToSocialNetworksTileTest extends TestCase
{
    public function testGetGroupName(): void
    {
        $this->assertEquals('Переходы', TransitionToSocialNetworksTile::getGroupName());
    }

    public function testGetGroupDescription(): void
    {
        $this->assertEquals(
            'Столько пользователей перешло на сайт или соц. сети вашей компании из профиля за последние 30 дней.',
            TransitionToSocialNetworksTile::getGroupDescription()
        );
    }

    public function testGetDataForView(): void
    {
        $expectedCount = 42;
        $expectedChangeType = StatisticsChangeType::negativeChangeType();

        $openSocialMonthGroupStatistic = new TransitionToSocialNetworksTile($expectedCount, $expectedChangeType);

        $this->assertEquals($expectedCount, $openSocialMonthGroupStatistic->getCount());
        $this->assertTrue($expectedChangeType->equals($openSocialMonthGroupStatistic->getMonthChangeType()));
    }
}
