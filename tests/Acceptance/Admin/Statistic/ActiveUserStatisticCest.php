<?php

namespace Tests\Acceptance\Admin\Statistic;

use Codeception\Util\HttpCode;
use Tester;

class ActiveUserStatisticCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/statistic/active-users/');
    }

    public function seeActiveUsersStatistic(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Статистика: активные пользователи');
    }

    public function seeActiveUsersStatisticNoDataMessage(Tester $I): void
    {
        $I->fillField('#statistic_periodFrom', '2000-01-01');
        $I->fillField('#statistic_periodTo', '2000-01-31');
        $I->click('Показать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Отсутствуют данные для построения графика.');
    }
}
