<?php

namespace Tests\Acceptance\Company\Access;

use Page\AccessCheckPages;
use Tester;

class CompanyAccessCreateCest
{
    private string $createCompanyPage;

    public function _before(): void
    {
        $this->createCompanyPage = '/companies/create/';
    }
    
    public function canSeeCreateAccessAllowedCest(Tester $I): void
    {
        $isAllowed = function (Tester $I) {
            $I->seeInSource('Компании');
        };

        $accessCheckPages = new AccessCheckPages($I, [$this->createCompanyPage], AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($I->findNotBannedUser(), $isAllowed, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findAdmin(), $isAllowed, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModerator(), $isAllowed, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findModeratorABM(), $isAllowed, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
        ;

        $accessCheckPages->assert();
    }
}
