<?php

namespace Tests\Acceptance\Admin\User;

use Codeception\Util\HttpCode;
use Tests\Support\TransferObject\User;
use Tester;

class AuthAsUserCest
{
    private const SUBSTITUTE_PAGE = '/admin/user/auth-as-user/';
    private const SUBSTITUTE_HEADER = 'Войти под другим пользователем';
    private const HOME_PAGE = '/';
    private const PROFILE_PAGE = '/users/profile/';

    /** @var User */
    private $admin;

    /** @var User */
    private $user;

    public function _before(Tester $I): void
    {
        $this->admin = $I->findAdmin();
        $this->user = $I->findNotBannedUser();
    }

    public function authAsUser(Tester $I): void
    {
        $I->authAs($this->admin);
        $I->amOnPage(self::SUBSTITUTE_PAGE);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see(self::SUBSTITUTE_HEADER);

        $I->fillField('auth_as_user[user]', $this->user->username);
        $I->click('form[name=auth_as_user] button');

        $I->seeCurrentUrlEquals(self::HOME_PAGE);
        $I->see('Вы просматриваете страницу от лица ' . $this->user->username);
        $I->seeElement('.user__menu-avatar', ['href' => self::PROFILE_PAGE . $this->user->id . '/']);
    }
}
