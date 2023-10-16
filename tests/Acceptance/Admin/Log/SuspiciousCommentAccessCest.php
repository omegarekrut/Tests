<?php

namespace Tests\Acceptance\Admin\Log;

use Page\AccessCheckPages;
use Tester;

class SuspiciousCommentAccessCest
{
    private const SUSPICIOUS_COMMENTS_URL = '/admin/log/suspicious-comments/';

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->see('Подозрительные комментарии');
            $I->see('Подозрительные комментарии содержащие ссылки', 'h1');
        };

        $accessCheckPages = new AccessCheckPages($I, [self::SUSPICIOUS_COMMENTS_URL], AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }

    public function denyAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->amOnPage('/admin/');
            $I->dontSee('Подозрительные комментарии');
        };

        $accessCheckPages = new AccessCheckPages($I, [self::SUSPICIOUS_COMMENTS_URL], AccessCheckPages::STRATEGY_NOT_FOUND);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }
}
