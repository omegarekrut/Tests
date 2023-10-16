<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;

class NewsPageCest
{
    public function _before(Tester $I): void
    {
        $I->amOnPage('/news/');
    }

    public function seeNewsList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новости', 'h1');
    }

    public function seeNews(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function pagination(Tester $I): void
    {
        $I->click('.pagination a');
        $I->see('Страница 2');
        $I->see('Страница 2', 'title');
        $I->seeLink('Предыдущая', '/news/');
    }

    public function seeNewsFirstPage(Tester $I): void
    {
        $I->amOnPage('/news/page1');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function dontSeeDeferredNewsOnNewsPage(Tester $I): void
    {
        $I->dontSee('Deferred news title');
    }

    public function seeNotFoundPageForDeferredNewsOnNewsViewPage(Tester $I): void
    {
        $deferredNewsId = $I->grabFromDatabase('records', 'id', ['title' => 'Deferred news title']);

        $I->amOnPage(sprintf('/news/view/%s', $deferredNewsId));
        $I->seeResponseCodeIs(404);
        $I->see('Страница не найдена');
    }
}
