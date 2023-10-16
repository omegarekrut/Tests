<?php

namespace Tests\Acceptance\Admin\Statistic;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group statistic
 */
class RecordStatisticCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/statistic/new-records/');
    }

    public function seeNewRecordsStatistic(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Статистика: все записи');
    }

    public function seeNewRecordsStatisticNoDataMessage(Tester $I): void
    {
        $I->fillField('#statistic_periodFrom', '2000-01-01');
        $I->fillField('#statistic_periodTo', '2000-01-31');
        $I->click('Показать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Отсутствуют данные для построения графика.');
    }
}
