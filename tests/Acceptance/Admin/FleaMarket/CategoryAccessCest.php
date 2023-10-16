<?php

namespace Tests\Acceptance\Admin\FleaMarket;

use Codeception\Util\HttpCode;
use Page\AccessCheckPages;
use Tester;

class CategoryAccessCest
{
    private $pages = [
        '/admin/flea-market/categories/',
        '/admin/flea-market/categories/create/',
    ];

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->see('Категории');
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
            $I->dontSee('Категории');
        };

        $pages = array_merge($this->pages, [
            '/admin/flea-market/categories/test/edit/',
            '/admin/flea-market/categories/test/delete/',
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
