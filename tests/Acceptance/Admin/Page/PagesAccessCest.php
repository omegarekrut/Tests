<?php

namespace Tests\Acceptance\Admin\Page;

use Page\AccessCheckPages;
use Tester;

/**
 * @group page
 */
class PagesAccessCest
{
    private $pages = [
        '/admin/page/',
        '/admin/page/create/',
    ];

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->seeInSource('>Страницы<');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }

    public function denyAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->dontSeeInSource('>Страницы<');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_NOT_FOUND);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }
}
