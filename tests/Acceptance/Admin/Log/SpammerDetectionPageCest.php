<?php

namespace Tests\Acceptance\Admin\Log;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group spam-detection
 */
class SpammerDetectionPageCest
{
    public function seeSuspiciousComments(Tester $I): void
    {
        $I->authAs($I->findAdmin());

        $I->amOnPage('/admin/log/spammer-detection/');

        $I->see('Обнаруженные спамеры');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
