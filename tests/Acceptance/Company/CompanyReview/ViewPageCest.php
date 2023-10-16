<?php

namespace Tests\Acceptance\Company\CompanyReview;

use Tester;

class ViewPageCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());
    }

    public function seeViewPage(Tester $I): void
    {
        $review = $I->grabActiveRecordByType('company_review');

        $I->amOnPage(sprintf('company-reviews/view/%d', $review['id']));

        $I->seeResponseCodeIsSuccessful();
        $I->see($review['title']);
    }

    public function testViewCountIncrementingAfterView(Tester $I): void
    {
        $review = $I->grabActiveRecordByType('company_review');
        $viewCountBefore = $review['views'];

        $I->amOnPage(sprintf('company-reviews/view/%d', $review['id']));
        $viewCountAfter = (int) $I->grabTextFrom('.articleFS__count__views');

        $I->assertEquals($viewCountBefore + 1, $viewCountAfter);
    }

    public function testAddComment(Tester $I): void
    {
        $reviewId = $I->grabActiveRecordIdByType('company_review');

        $I->amOnPage(sprintf('company-reviews/view/%d', $reviewId));

        $I->fillField('create_comment[text]', 'My test comment');
        $I->click('Написать');

        $I->see('My test comment');
    }
}
