<?php

namespace Tests\Acceptance\Company\CompanyArticle;

use Tester;

class ViewPageCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());
    }

    public function seeViewPage(Tester $I): void
    {
        $article = $I->grabActiveRecordByType('company_article');

        $I->amOnPage(sprintf('company-articles/view/%d', $article['id']));

        $I->seeResponseCodeIsSuccessful();
        $I->see($article['title']);
    }

    public function see404OnHiddenCompanyArticle(Tester $I): void
    {
        $hiddenCompanyArticleId = $I->grabHiddenCompanyArticleId();

        $I->amOnPage(sprintf('company-articles/view/%d', $hiddenCompanyArticleId));
        $I->seePageNotFound();
    }

    public function testViewCountIncrementingAfterView(Tester $I): void
    {
        $article = $I->grabActiveRecordByType('company_article');
        $viewCountBefore = $article['views'];

        $I->amOnPage(sprintf('company-articles/view/%d', $article['id']));
        $viewCountAfter = (int) $I->grabTextFrom('.articleFS__count__views');

        $I->assertEquals($viewCountBefore + 1, $viewCountAfter);
    }

    public function testAddComment(Tester $I): void
    {
        $articleId = $I->grabActiveRecordIdByType('company_article');

        $I->amOnPage(sprintf('company-articles/view/%d', $articleId));

        $I->fillField('create_comment[text]', 'My test comment');
        $I->click('Написать');

        $I->see('My test comment');
    }
}
