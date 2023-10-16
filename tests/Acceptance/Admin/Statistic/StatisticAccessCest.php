<?php

namespace Tests\Acceptance\Admin\Statistic;

use Page\AccessCheckPages;
use Tester;

/**
 * @group statistic
 */
class StatisticAccessCest
{
    private $pages = [
        '/admin/statistic/new-users/',
        '/admin/statistic/new-comments/',
        '/admin/statistic/new-records/',
        '/admin/statistic/new-tidings/',
        '/admin/statistic/new-articles/',
        '/admin/statistic/new-gallery/',
        '/admin/statistic/new-videos/',
        '/admin/statistic/active-users/',
        '/admin/statistic/new-maps/',
    ];

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->seeInSource('>Статистика: ');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }

    public function denyAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->dontSeeInSource('>Статистика: ');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_NOT_FOUND);
        $accessCheckPages
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }
}
