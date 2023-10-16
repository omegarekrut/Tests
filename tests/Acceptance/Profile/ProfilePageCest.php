<?php

namespace Tests\Acceptance\Profile;

use Codeception\Scenario;
use Codeception\Util\HttpCode;
use Tester;

class ProfilePageCest
{
    private $user;

    public function _before(Tester $I): void
    {
        $this->user = $I->findNotBannedUser();
        $I->authAs($this->user);
        $I->see('выход');
    }

    public function seeMainProfilePage(Tester $I): void
    {
        $this->amOnProfilePage($I);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeProfilePageSelfAdsSidebar(Tester $I): void
    {
        $this->amOnProfilePage($I);
        $I->see('Мои объявления');
    }

    public function seeComments(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserCommentsInDatabase($this->user) === false) {
            $scenario->skip('User has no comments');
        }

        $this->amOnProfilePage($I, 'comments');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeTidings(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserRecordsInDatabase($this->user, 'tidings') === false) {
            $scenario->skip('User has no tidings');
        }

        $this->amOnProfilePage($I, 'tidings');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeArticles(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserRecordsInDatabase($this->user, 'article') === false) {
            $scenario->skip('User has no articles');
        }

        $this->amOnProfilePage($I, 'articles');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeGallery(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserRecordsInDatabase($this->user, 'gallery') === false) {
            $scenario->skip('User has no gallery');
        }

        $this->amOnProfilePage($I, 'gallery');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeVideo(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserRecordsInDatabase($this->user, 'video') === false) {
            $scenario->skip('User has no videos');
        }

        $this->amOnProfilePage($I, 'video');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeTackleReviews(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserRecordsInDatabase($this->user, 'tackle_review') === false) {
            $scenario->skip('User has no tackle reviews');
        }

        $this->amOnProfilePage($I, 'tackle_reviews');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeMapPoints(Tester $I, Scenario $scenario): void
    {
        if ($I->seeUserRecordsInDatabase($this->user, 'map') === false) {
            $scenario->skip('User has no maps');
        }

        $this->amOnProfilePage($I, 'maps');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function amOnProfilePage(Tester $I, string $section = ''): void
    {
        $I->amOnPage(sprintf('/users/profile/%d/%s', $this->user->id, $section));
    }
}
