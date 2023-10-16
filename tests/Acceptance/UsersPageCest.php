<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;

class UsersPageCest
{
    public function _before(Tester $I): void
    {
        $I->amOnPage('/users/');
    }

    public function seeUsersList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Список пользователей сайта');
    }

    public function searchUserByUsername(Tester $I): void
    {
        $user = $I->findNotBannedUser();

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField('#search', $user->username);
        $I->click('#search_button');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($user->username, '.users-list a.users-list-login__login');
    }

    public function pagination(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('.pagination a');
        $I->see('Страница 2');
        $I->see('Страница 2', 'title');
    }

    public function sortUsersByLogin(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Логин, Имя, Откуда');
        $I->see('Логин, Имя, Откуда', '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Логин, Имя, Откуда');
        $I->see('Логин, Имя, Откуда', '.asc');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortUsersByRegistrationDate(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Как давно зарегистрирован');
        $I->see('Как давно зарегистрирован', '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('Как давно зарегистрирован');
        $I->see('Как давно зарегистрирован', '.asc');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortUsersByActivityRating(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $activityText = sprintf('Активность в %d году', date('Y'));

        $I->click($activityText);
        $I->see($activityText, '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click($activityText);
        $I->see($activityText, '.asc');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortUsersByGlobalRating(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);

        $globalratingText = 'Общий рейтинг';

        $I->click($globalratingText);
        $I->see($globalratingText, '.asc');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click($globalratingText);
        $I->see($globalratingText, '.desc');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @return integer[]
     */
    private function grabProfileIds(Tester $I): array
    {
        return array_map(function ($item) {
            $match = [];
            if (preg_match('/\/users\/profile\/([\d]+)\//i', $item, $match)) {
                return intval($match[1]);
            }

            return 0;
        }, $I->grabMultiple('.users-list a.users-list-login__login', 'href'));
    }

    public function ajaxUsernameSearchByQuery(Tester $I): void
    {
        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest('/username/search/?q=');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->see('usernames');
    }

    public function lookAtSortIcons(Tester $I): void
    {
        $I->assertEquals(1, $this->countUserSortIcons($I));

        $I->click('Логин, Имя, Откуда');
        $I->seeElement('.users-list .users-list__header_login .iconFS--sort-desc');
        $I->assertEquals(1, $this->countUserSortIcons($I));

        $I->click('Логин, Имя, Откуда');
        $I->seeElement('.users-list .users-list__header_login .iconFS--sort-asc');
        $I->assertEquals(1, $this->countUserSortIcons($I));

        $I->click('Как давно зарегистрирован');
        $I->seeElement('.users-list .users-list__header_registration .iconFS--sort-asc');
        $I->assertEquals(1, $this->countUserSortIcons($I));

        $I->click(sprintf('Активность в %d году', date('Y')));
        $I->seeElement('.users-list .users-list__header_rating .iconFS--sort-asc');
        $I->assertEquals(1, $this->countUserSortIcons($I));

        $I->click('Общий рейтинг');
        $I->seeElement('.users-list .users-list__header_rating .iconFS--sort-asc');
        $I->assertEquals(1, $this->countUserSortIcons($I));
    }

    private function countUserSortIcons(Tester $I): int
    {
        return count($I->grabMultiple('.users-list .iconFS--sort-asc')) + count($I->grabMultiple('.users-list .iconFS--sort-desc'));
    }
}
