<?php

namespace Tests\Acceptance\Admin\WaterLevel\GaugingStation;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group water-level
 */
class GaugingStationCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/gauging-station/');
    }

    public function seeGaugingStationList(Tester $I): void
    {
        $I->see('Гидропосты');
    }

    public function filterGaugingStationListByWaterName(Tester $I): void
    {
        $waterNames = $I->grabAllWaterNames();

        $unexpectedWaterNames = $waterNames;
        $expectedWaterName = array_shift($unexpectedWaterNames);

        $I->selectOption('#gauging_station_search_water', $expectedWaterName);
        $I->click('Поиск');

        $I->seeResponseCodeIs(HttpCode::OK);

        foreach ($unexpectedWaterNames as $unexpectedWaterName) {
            $I->dontSee($unexpectedWaterName, 'td');
        }
    }

    public function editGaugingStation(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование гидропоста');

        $name = $I->getFaker()->city;
        $distanceFromSource = $I->getFaker()->numberBetween(1, 100);
        $distanceToEstuary = $I->getFaker()->numberBetween(1, 100);
        $seaLevelHeight = $I->getFaker()->numberBetween(1, 100);

        $I->fillField('gauging_station_update[name]', $name);
        $I->fillField('gauging_station_update[distanceFromSourceInKilometers]', $distanceFromSource);
        $I->fillField('gauging_station_update[distanceToEstuaryInKilometers]', $distanceToEstuary);
        $I->fillField('gauging_station_update[seaLevelHeight]', $seaLevelHeight);

        $I->click('Сохранить');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Гидропост успешно обновлен.');
    }

    public function changeVisibilityGaugingStation(Tester $I): void
    {
        $I->click('//a[@title="Скрыть запись"]');
        $I->see('Список гидропостов');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись скрыта.');

        $I->click('//a[@title="Восстановить запись"]');
        $I->see('Список гидропостов');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись восстановлена.');
    }
}
