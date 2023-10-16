<?php

namespace Tests\Acceptance\Admin\WaterLevel\Water;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group water-level
 */
class WaterCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/water/');
    }

    public function seeWaterList(Tester $I): void
    {
        $I->see('Водные объекты');
    }

    public function seeEditWaterPage(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование водного объекта');
    }

    public function updateWater(Tester $I): void
    {
        $allWatersIdAndName = $I->grabAllWatersIdAndName();
        $waterToUpdateIdAndName = $I->getFaker()->randomElement($allWatersIdAndName);

        $I->amOnPage(sprintf('/admin/water/%s/update', $waterToUpdateIdAndName['id']));

        array_splice($allWatersIdAndName, array_search($waterToUpdateIdAndName, $allWatersIdAndName), 1);
        $watersToSelectParentWater = $allWatersIdAndName;
        $parentWaterIdAndName = $I->getFaker()->randomElement($watersToSelectParentWater);

        $I->selectOption('#water_parentWater', $parentWaterIdAndName['name']);
        $I->fillField('water[distanceFromParentWaterSourceInKilometers]', $I->getFaker()->numberBetween(100, 200));

        $I->click('Сохранить');

        try {
            $I->seeResponseCodeIs(HttpCode::OK);
            $I->seeAlert('success', 'Водный объект успешно обновлен.');
        } finally {
            $I->resetParentIdForWater($waterToUpdateIdAndName['id']);
        }
    }
}
