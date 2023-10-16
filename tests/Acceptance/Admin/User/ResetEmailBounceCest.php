<?php

namespace Tests\Acceptance\Admin\User;

use Codeception\Util\HttpCode;
use Tester;

class ResetEmailBounceCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
    }

    public function resetEmailBounceForUser(Tester $I): void
    {
        $user = $I->findBannedUser();

        $I->amOnPage('/admin/user/reset-email-bounce/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Разблокировать e-mail пользователя');

        $I->fillField('reset_email_bounce[user]', $user->username);
        $I->click('Сохранить');

        $I->seeAlert('success', 'E-mail успешно разбанен.');
    }
}
