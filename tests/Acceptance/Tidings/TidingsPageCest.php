<?php

namespace Tests\Acceptance\Tidings;

use Carbon\Carbon;
use Codeception\Util\HttpCode;
use Tester;

class TidingsPageCest
{
    public function _before(Tester $I): void
    {
        $I->amOnPage('/tidings/');
    }

    public function seeTidingsList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Вести с водоемов', 'h1');
    }

    public function seeTiding(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function searchTidingByQuery(Tester $I): void
    {
        $searchQueryString = $I->grabFromDatabase('records', 'title', ['type' => 'tidings']);
        $I->fillField('#search', $searchQueryString);
        $I->click('[type="submit"]', '.contentFS__header');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($searchQueryString, 'h1');
    }

    public function userCanSeeAddCommentForm(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());
        $I->click('a.articleFS__content__link');
        $I->seeElement('form[name="create_comment"]');
    }

    public function pagination(Tester $I): void
    {
        $I->click('.pagination a');
        $I->see('Страница 2');
        $I->see('Страница 2', 'title');
        $I->seeLink('Предыдущая', '/tidings/');
    }

    public function seeTidingsFirstPage(Tester $I): void
    {
        $I->amOnPage('/tidings/page1');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
