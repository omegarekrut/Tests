<?php

namespace Tests\Acceptance\Admin\Log;

use Codeception\Util\HttpCode;
use Tester;

class CommentPageCest
{
    public function seeComments(Tester $I): void
    {
        $I->authAs($I->findAdmin());

        $I->amOnPage('/admin/log/comments/');

        $I->see('Комментарии за последнюю неделю');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
