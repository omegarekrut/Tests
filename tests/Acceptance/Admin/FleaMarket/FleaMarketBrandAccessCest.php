<?php

namespace Tests\Acceptance\Admin\FleaMarket;

use Codeception\Util\HttpCode;
use Page\AccessCheckPages;
use Tester;

class FleaMarketBrandAccessCest
{
    private $pages = [
        '/admin/flea-market/brands/',
        '/admin/flea-market/brands/create/',
    ];

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->see('Бренды');
            $I->seeResponseCodeIs(HttpCode::OK);
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }

    public function denyAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->dontSee('Бренды');
        };

        $pages = array_merge($this->pages, [
                '/admin/flea-market/brands/test/edit/',
                '/admin/flea-market/brands/test/delete/',
            ]
        );

        $accessCheckPages = new AccessCheckPages($I, $pages, AccessCheckPages::STRATEGY_NOT_FOUND);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }
}
