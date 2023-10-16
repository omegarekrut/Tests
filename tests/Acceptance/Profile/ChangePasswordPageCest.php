<?php

namespace Tests\Acceptance\Profile;

use Codeception\Util\HttpCode;
use Tester;

class ChangePasswordPageCest
{
    public function changePasswordOnProfilePage(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $I->authAs($user);
        $I->amOnPage('/profile/edit/password/');
        $I->seeResponseCodeIs(HttpCode::OK);

        $newPassword = time();

        $I->fillField('change_password[oldPassword]', $user->password);
        $I->fillField('change_password[newPassword]', $newPassword);

        $I->click('Сохранить');

        $I->updateInDatabase('users', ['password' => $user->password], ['id' => $user->id]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Новый пароль успешно установлен.');
    }
}
