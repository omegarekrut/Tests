<?php

namespace Tests\Acceptance\Company\CompanyArticle;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group company
 */
class CompanyArticlePageCest
{
    private const INDEX_PAGE = '/companies/news/';

    public function _before(Tester $I): void
    {
        $I->amOnPage(self::INDEX_PAGE);
    }

    public function seeCompanyArticleIndexPage(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новости компаний', 'h1');
    }

    public function seeCompanyArticleInfoSelectorIndexPage(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новости компаний', 'span');
        $I->see('Каталог', 'span');
        $I->seeElement('.author__block__name');
        $I->seeElement('.author__block__dateActivity');
        $I->seeElement('.articleFS__count__comments');
        $I->seeElement('.articleFS__count__views');
        $I->seeElement('.rating__block');
    }

    public function seeCompanyArticle(Tester $I): void
    {
        $I->click('a.author__block__name');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeArticleFindHeader(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function dontSeeCompanyArticleFirstPage(Tester $I): void
    {
        $I->amOnPage(self::INDEX_PAGE . 'page1/');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function userCanSeeAddCommentForm(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());
        $I->click('a.articleFS__content__link');
        $I->seeElement('form[name="create_comment"]');
    }
}
