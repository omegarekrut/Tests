<?php

namespace Tests\Acceptance\Admin\WeeklyLetter;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group weekly-letter
 */
class WeeklyLetterCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/weekly-letter/');
    }

    public function seeWeeklyLetterList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Рассылки');
    }
}
