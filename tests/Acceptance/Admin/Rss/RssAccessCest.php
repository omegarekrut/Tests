<?php

namespace Tests\Acceptance\Admin\Rss;

use Page\AccessCheckPages;
use Tester;

class RssAccessCest
{
    private $pages = [
        '/admin/rss/',
    ];

    public function denyAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->dontSee('Яндекс.Дзен');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_NOT_FOUND);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->see('Яндекс.Дзен');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }
}
