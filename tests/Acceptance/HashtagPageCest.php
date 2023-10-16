<?php

namespace Tests\Acceptance;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group hashtags
 */
class HashtagPageCest
{
    public function ajaxHashtagSearchByQuery(Tester $I): void
    {
        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxGetRequest('/hashtag/search/?q=');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
