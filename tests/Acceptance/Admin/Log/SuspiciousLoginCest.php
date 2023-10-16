<?php

namespace Tests\Acceptance\Admin\Log;

use Codeception\Util\HttpCode;
use Tester;

class SuspiciousLoginCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
    }

    public function seeSuspiciousLoginList(Tester $I): void
    {
        $I->amOnPage('/admin/log/suspicious_login/');
        $I->see('Подозрительные смены аккаунтов пользователями');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
