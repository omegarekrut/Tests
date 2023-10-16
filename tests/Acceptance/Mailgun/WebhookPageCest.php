<?php

namespace Tests\Acceptance\Mailgun;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group mailgun
 */
class WebhookPageCest
{
    public function invalidRequestToBounceUserEmailShouldGetSuccessResponse(Tester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->sendPOST('/mailgun/webhook/bounce-user-email/');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['status' => HttpCode::OK]);
    }

    public function invalidRequestToDoNotDisturbUserShouldGetSuccessResponse(Tester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->sendPOST('/mailgun/webhook/do-not-disturb-user/');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['status' => HttpCode::OK]);
    }
}
