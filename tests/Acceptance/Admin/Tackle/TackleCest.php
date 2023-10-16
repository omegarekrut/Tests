<?php

namespace Tests\Acceptance\Admin\Tackle;

use Codeception\Scenario;
use Tester;

class TackleCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/tackles/');
    }

    public function seeTackleList(Tester $I): void
    {
        $I->see('Список снастей');
    }

    public function hideTackle(Tester $I, Scenario $scenario): void
    {
        $firstId = (int) $I->grabTextFrom('td');
        if (empty($firstId)) {
            $scenario->skip('Not found tackle ID in html');
        }
        $I->amOnPage(sprintf('/admin/tackle/%d/hide/', $firstId));
        $I->amOnPage('/admin/tackles/');
        $I->dontSeeInSource(">$firstId<");
    }
}
