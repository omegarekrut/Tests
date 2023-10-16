<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;

class AdvertisingPageCest
{
    public function seeAdvertisingPage(Tester $I): void
    {
        $I->amOnPage('/reklama/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Реклама на проекте Fishingsib.ru', 'h1');
    }

    public function askQuestion(Tester $I): void
    {
        $I->sendAjaxGetRequest('/advertising/ask_question/');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function subscribe(Tester $I): void
    {
        $I->sendAjaxGetRequest('/advertising/price-list/');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
