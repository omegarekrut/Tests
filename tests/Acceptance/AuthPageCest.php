<?php

namespace Tests\Acceptance;

use Tester;

class AuthPageCest
{
    private $user;

    public function _before(Tester $I): void
    {
        $this->user = $this->user ?: $I->findNotBannedUser();
    }

    public function checkAuthOnLoginPage(Tester $I): void
    {
        $I->amOnPage('/login');
        $I->see('Авторизация на сайте');

        $I->authAs($this->user);
        $I->see('выход');
        $I->assertEquals('/', $I->getCurrentUrl());
    }

    public function checkSidebarAuthWithRememberCookie(Tester $I): void
    {
        $I->authAs($this->user, true);
        $I->see('выход');

        $cookieHash = $I->grabCookie('remember_me');
        $I->assertNotEmpty($cookieHash);
    }

    public function checkSidebarAuthWithoutRememberCookie(Tester $I): void
    {
        $I->authAs($this->user, false);
        $I->see('выход');

        $cookieHash = $I->grabCookie('remember_me');
        $I->assertIsEmpty($cookieHash);
    }

    public function logout(Tester $I): void
    {
        $I->authAs($this->user);
        $I->logout();
        $I->seeResponseCodeIs(200);
        $I->see('Зарегистрироваться');
    }

    public function invalidLogin(Tester $I): void
    {
        $I->amOnPage('/login/');
        $I->submitForm('.login__auth form', [
            'login[login]' => 'INVALID_LOGIN',
            'login[password]' => 'INVALID_PASSWORD',
        ]);
        $I->seeResponseCodeIs(200);

        $I->seeAlert('error', 'Неверный логин или пароль.');
    }

    public function redirectAfterLogin(Tester $I): void
    {
        $I->amOnPage('/companies/create/');
        $I->see('Авторизация на сайте');

        $I->seeCurrentUrlEquals('/login/?_target_path=/companies/create/');

        $I->submitForm('(//form[@name="login"])[1]', [
            'login[login]' => $this->user->username,
            'login[password]' => $this->user->password,
        ]);

        $I->seeCurrentUrlEquals('/companies/create/');
    }

    public function redirectAfterLoginViaCommentAuthForm(Tester $I)
    {
        $tidingsId = $I->grabActiveTidingsId();
        $tidingsUrl = sprintf('/tidings/view/%s', $tidingsId);

        $I->amOnPage($tidingsUrl);

        $I->see('Войдите на сайт, чтобы оставлять комментарии.');
        $I->click('Войти');

        $I->submitForm('(//form[@name="login"])[2]', [
            'login[login]' => $this->user->username,
            'login[password]' => $this->user->password,
        ]);

        $I->seeCurrentUrlEquals($tidingsUrl);
    }
}
