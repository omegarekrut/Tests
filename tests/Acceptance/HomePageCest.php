<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;

class HomePageCest
{
    public function seeInterestingPage(Tester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новое на сайте', 'h1');
    }

    public function seeFreshPage(Tester $I): void
    {
        $I->amOnPage('/fresh/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новое на сайте', 'h1');
    }

    public function seeCompanyNewsPage(Tester $I): void
    {
        $I->amOnPage('/companies-news/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новое на сайте', 'h1');
    }

    public function dontSeeDeferredNewsOnHomePage(Tester $I): void
    {
        $I->amOnPage('/');
        $I->dontSee('Deferred news title');
    }

    public function dontSeeDeferredNewsOnFreshPage(Tester $I): void
    {
        $I->amOnPage('/fresh/');
        $I->dontSee('Deferred news title');
    }

    public function dontSeeDeferredNewsOnCompanyNewsPage(Tester $I): void
    {
        $I->amOnPage('/companies-news/');
        $I->dontSee('Deferred news title');
    }
}
