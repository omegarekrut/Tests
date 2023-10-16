<?php

namespace Tests\Acceptance\Profile;

use Codeception\Scenario;
use Codeception\Util\HttpCode;
use Tester;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

class EmptyProfilePageCest
{
    private $user;

    public function _before(Scenario $scenario, Tester $I): void
    {
        try {
            $this->user = $I->findUserByCriteria([
                'userId' => $this->grabUserId($I),
                'group' => 'user',
            ]);
        } catch (\Throwable $exception) {
            $scenario->skip(sprintf('Not found user with login "%s" from group user', LoadUserWithoutRecords::REFERENCE_NAME));
        }

        $I->authAs($this->user);
        $I->see('выход');
    }

    public function seeMainProfilePage(Tester $I): void
    {
        $this->amOnProfilePage($I);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeComments(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'comments');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeTidings(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'tidings');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeArticles(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'articles');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeGallery(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'gallery');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeVideo(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'video');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeTackleReviews(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'tackle_reviews');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeMapPoints(Tester $I, Scenario $scenario): void
    {
        $this->amOnProfilePage($I, 'maps');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    private function amOnProfilePage(Tester $I, string $section = ''): void
    {
        $I->amOnPage(sprintf('/users/profile/%d/%s', $this->user->id, $section));
    }

    private function grabUserId(Tester $I): int
    {
        return (int) $I->grabFromDatabase('users', 'id', [
            'login' => LoadUserWithoutRecords::REFERENCE_NAME,
        ]);
    }
}
