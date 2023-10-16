<?php

namespace Tests\Acceptance\Profile;

use Codeception\Util\HttpCode;
use Tester;

class NotificationPageCest
{
    public function checkAccessUserToNotificationPopupContent(Tester $I): void
    {
        $user = $I->findNotBannedUser();
        $I->authAs($user);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest('/profile/notifications/popup/');

        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
