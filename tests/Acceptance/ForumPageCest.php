<?php

namespace Tests\Acceptance;

use Codeception\Exception\ElementNotFound;
use Codeception\Util\HttpCode;
use Tester;

class ForumPageCest
{
    public function templateWithMinimalDependencies(Tester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/frm/', [
            "manOfTheWeek" => null,
            "sidebarLastThreads" => [],
            "routes" => [
                'home' => '#',
                'forum' => '#',
                'threads' => '#',
                'active_thread' => '#',
                'conversations' => '#',
                'conversation_add' => '#',
                'account_alerts' => '#',
                'watched_threads' => '#',
                'profile' => '#',
                'profile_message' => '#',
                'profile_edit' => '#',
                'adminStatistics' => '#',
            ],
            "profile" => null,
        ]);
        $I->canSeeResponseCodeIs(200);

        $page = $I->grabResponse();

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->assertStringContainsString('<!-- BODY-CONTENT -->', $page);
        $I->assertStringContainsString('<!-- BODY-CONFIG -->', $page);
        $I->assertStringContainsString('<!-- HEAD-CONTENT -->', $page);
        $I->assertStringContainsString('<!-- HTML-CONFIG -->', $page);
    }

    public function forumWebhookProcessorMustBeAvailable(Tester $I): void
    {
        $I->sendPOST('/frm/webhook/', []);
        $I->canSeeResponseCodeIs(200);
    }

    public function forumWebhookCanReceiveAndProcessUserReceivedAlertEvent(Tester $I): void
    {
        /** @var \Tests\Support\TransferObject\User $user */
        $user = $I->findNotBannedUser();
        /** @var \Tests\Support\TransferObject\User $initiator */
        $initiator = $I->findAnotherUserInGroup($user);

        $I->authAs($user);

        $I->sendAjaxGetRequest('/api/profile/me/');

        $stringResponse = $I->grabResponse();
        $response = json_decode($stringResponse, true);

        $unreadCountNotification = $response['countNotification'];

        $I->sendPOST('/frm/webhook/', json_encode([
            [
                'event' => 'user.alert_received',
                'data' => [
                    'alertId' => random_int(0, 100),
                    'alertedUserId' => (int) $user->forumUserId,
                    'userId' => (int) $initiator->forumUserId,
                    'action' => 'like',
                    'htmlMessage' => 'some like message',
                ],
            ],
        ]));

        $I->sendAjaxGetRequest('/api/profile/me/');

        $stringResponse = $I->grabResponse();
        $response = json_decode($stringResponse, true);

        $I->assertEquals($unreadCountNotification + 1, $response['countNotification']);
    }
}
