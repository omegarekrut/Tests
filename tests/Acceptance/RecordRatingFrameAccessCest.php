<?php

namespace Tests\Acceptance;

use Page\AccessCheckPages;
use Tester;

class RecordRatingFrameAccessCest
{
    private $pages;

    public function _before(Tester $I): void
    {
        $recordId = $I->grabFromDatabase('records', 'id', [
            'active' => 1,
            'deleted_at' => null,
        ]);

        $this->pages = [
            sprintf('/admin/records/rating/%s/', $recordId),
        ];
    }

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->see('Список проголосовавших', 'b');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());
        $accessCheckPages->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());
        $accessCheckPages->assert();
    }

    public function denyAccess(Tester $I): void
    {
        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_DENIED);
        $accessCheckPages->addTest($I->findNotBannedUser(), null, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());
        $accessCheckPages->addTest($I->findModerator(), null, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());
        $accessCheckPages->assert();
    }

    public function denyShowForGuest(Tester $I): void
    {
        $I->amOnPage($this->pages[0]);
        $I->dontSee('Список проголосовавших', 'b');
        $I->see('Авторизация на сайте');
    }
}
