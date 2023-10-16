<?php

namespace Tests\Acceptance\Admin\User;

use Codeception\Util\HttpCode;
use Tester;

class DeleteManyUsersCest
{
    public function seeUsers(Tester $I): void
    {
        $I->authAs($I->findAdmin());

        $I->amOnPage('/admin/user/choose-many/');

        $I->see('Удаление пользователей');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
