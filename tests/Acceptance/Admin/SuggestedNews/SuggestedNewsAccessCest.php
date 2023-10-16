<?php

namespace Tests\Acceptance\Admin\SuggestedNews;

use Page\AccessCheckPages;
use Tester;

/**
 * @group suggested-news
 */
class SuggestedNewsAccessCest
{
    private array $pages = [
        '/admin/suggested-news/',
    ];

    public function allowAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->seeInSource('>Список предложенных новостей<');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findModerator(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findAdmin(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }

    public function denyAccess(Tester $I): void
    {
        $testClosure = function (Tester $I) {
            $I->dontSeeInSource('>Список предложенных новостей<');
        };

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_NOT_FOUND);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $testClosure, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->getAnonymousUser(), $testClosure);

        $accessCheckPages->assert();
    }
}
