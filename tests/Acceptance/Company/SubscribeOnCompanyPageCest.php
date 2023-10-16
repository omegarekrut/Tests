<?php

namespace Tests\Acceptance\Company;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group subscription
 */
class SubscribeOnCompanyPageCest
{
    private const COMPANY_SUBSCRIBE_URI_TEMPLATE = '/subscribe-to-company/%s/';
    private const COMPANY_UNSUBSCRIBE_URI_TEMPLATE = '/unsubscribe-from-company/%s/';

    public function notBannedUserCanSubscribeOnCompany(Tester $I): void
    {
        $notBannedUser = $I->findNotBannedUser();
        $I->authAs($notBannedUser);

        $notSubscribedCompany = $I->grabPublicCompanyThatIsNotInUserSubscriptions($notBannedUser->id);
        $subscribeUrl = sprintf(self::COMPANY_SUBSCRIBE_URI_TEMPLATE, $notSubscribedCompany->shortUuid);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest(trim($subscribeUrl, '\''));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['state' => true]);
    }

    public function notBannedUserCanUnsubscribeToCompany(Tester $I): void
    {
        $notBannedUser = $I->findNotBannedUser();
        $I->authAs($notBannedUser);

        $subscribedCompany = $I->grabPublicCompanyThatIsInUserSubscription($notBannedUser->id);
        $unsubscribeUrl = sprintf(self::COMPANY_UNSUBSCRIBE_URI_TEMPLATE, $subscribedCompany->shortUuid);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest(trim($unsubscribeUrl, '\''));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['state' => false]);
    }
}
