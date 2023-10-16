<?php

namespace Tests\Acceptance\Admin\Log;

use Codeception\Util\HttpCode;
use Tester;

class SuspiciousCommentPageCest
{
    public function seeSuspiciousComments(Tester $I): void
    {
        $I->authAs($I->findAdmin());

        $I->amOnPage('/admin/log/suspicious-comments/');

        $I->see('Подозрительные комментарии содержащие ссылки');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
