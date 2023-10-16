<?php

namespace Tests\Acceptance\Admin\WaterLevel\NearbyGaugingStation;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group water-level
 */
class NearbyGaugingStationTableCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
    }

    public function eliminationConflictGaugingStation(Tester $I): void
    {
        $I->amOnPage('/admin/nearby-gauging-station/with-conflict/');
        $I->see('Одинаковые гидропосты');

        $gaugingStation = $I->grabTextFrom('//table/tbody/tr/td[2]');
        $I->click('//a[@title="Скрыть запись"]');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись скрыта.');

        $I->amOnPage('/admin/nearby-gauging-station/without-conflict/');
        $I->see($gaugingStation);

        $xpathRestore = sprintf('//table/tbody/tr/td[2][contains(text(), "%s")]/../td[last()]/div/a[@title="Восстановить запись"]', $gaugingStation);
        $I->click($xpathRestore);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись восстановлена.');

        $I->amOnPage('/admin/nearby-gauging-station/with-conflict/');
        $I->see('Одинаковые гидропосты');
        $I->see($gaugingStation);
    }
}
