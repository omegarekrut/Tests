<?php

namespace Tests\Acceptance\Admin\SuggestedNews;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group suggested-news
 */
class SuggestedNewsCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/suggested-news/');
    }

    public function viewSuggestedNews(Tester $I): void
    {
        $I->click('//a[@title="Просмотреть"][1]');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function titleEqual(Tester $I): void
    {
        $title = $I->grabTextFrom('//table/tbody/tr[1]/td[1]');
        $I->click('//a[@title="Просмотреть"][1]');
        $I->see($title, 'h1');
    }
}
