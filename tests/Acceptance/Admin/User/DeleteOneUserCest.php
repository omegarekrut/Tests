<?php

namespace Tests\Acceptance\Admin\User;

use Codeception\Util\HttpCode;
use Tester;

class DeleteOneUserCest
{
    public function seeUserChoice(Tester $I): void
    {
        $I->authAs($I->findAdmin());

        $I->amOnPage('/admin/user/choose-one/');

        $I->see('Выбор пользователя для удаления');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
