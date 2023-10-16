<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;

class LandingPageCest
{
    private $landingSlug;

    public function _before(Tester $I): void
    {
        $this->landingSlug = $I->grabFromDatabase('landings', 'slug');
        $I->amOnPage("/landing/$this->landingSlug/");
    }

    public function seeLandingPage(Tester $I): void
    {
        $landingHeading = $I->grabFromDatabase('landings', 'heading', [
            'slug' => $this->landingSlug,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($landingHeading, 'h1');
    }

    /*
     * @todo there is no check on the created date because the date format in the page is not suitable
     */
    public function sortRecordsByCreatedAt(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Дате создания');
        $I->see('Дате создания', '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Дате создания');
        $I->see('Дате создания', '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortRecordsByRating(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Рейтингу');
        $I->see('Рейтингу', '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Рейтингу');
        $I->see('Рейтингу', '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $rating = $I->grabMultiple('.articles-page-list .rating__block__value--positive');
        $recordIds = $this->grabRecordIds($I);

        $I->seeOrderDescRecord($rating, $recordIds);
    }

    public function seeLandingFirstPage(Tester $I): void
    {
        $I->amOnPage("/landing/$this->landingSlug/page1");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * @return int[]
     */
    private function grabRecordIds(Tester $I): array
    {
        $recordIds = array_map(static function (string $link): ?int {
            $match = [];

            if (preg_match('/\/tidings\/view\/([\d]+)\//i', $link, $match)) {
                return intval($match[1]);
            }

            return null;
        }, $I->grabMultiple('.articles-page-list a.articleFS__content__link', 'href'));

        return array_filter($recordIds);
    }
}
