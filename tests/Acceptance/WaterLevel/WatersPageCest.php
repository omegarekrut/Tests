<?php

namespace Tests\Acceptance\WaterLevel;

use Tester;

/**
 * @group water-level
 */
class WatersPageCest
{
    private const WATERS_PAGE = '/waterinfo/waters/';

    private $waterShownNames;
    private $waterHiddenNames;

    public function _before(Tester $I): void
    {

        $this->waterShownNames = $I->grabShownWaterNames();
        $this->waterHiddenNames = array_diff($I->grabAllWaterNames(), $I->grabShownWaterNames());

        $I->amOnPage(self::WATERS_PAGE);
    }

    public function seeWatersWithShownGaugingStations(Tester $I): void
    {
        foreach ($this->waterShownNames as $waterName) {
            $I->see($waterName, '.waterLevel__waters-text');
        }
    }

    public function dontSeeWatersWithoutShownGaugingStations(Tester $I): void
    {
        foreach ($this->waterHiddenNames as $waterName) {
            $I->dontSee($waterName, '.waterLevel__waters-text');
        }
    }
}
