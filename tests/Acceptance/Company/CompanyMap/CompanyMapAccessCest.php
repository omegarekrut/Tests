<?php

namespace Tests\Acceptance\Company\CompanyMap;

use Page\AccessCheckPages;
use Tester;

class CompanyMapAccessCest
{
    private array $rubricWithCompany;
    private array $pages;

    public function _before(Tester $I): void
    {
        $this->rubricWithCompany = $I->findRubricWithCompany();

        $this->pages = [
            '/companies/ajax-markers/',
            sprintf('/companies/ajax-markers/rubrics/%s/', $this->rubricWithCompany['id']),
        ];
    }

    public function allowGetCompanyMarkersForAdmin(Tester $I): void
    {
        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTestWithAuth($I->findAdmin());

        $accessCheckPages->assert();
    }

    public function allowGetCompanyMarkersForModerator(Tester $I): void
    {
        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTestWithAuth($I->findModerator());

        $accessCheckPages->assert();
    }

    public function allowGetCompanyMarkersForNotBannedUser(Tester $I): void
    {
        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTestWithAuth($I->findNotBannedUser());

        $accessCheckPages->assert();
    }

    public function allowGetCompanyMarkersForAnonymousUser(Tester $I): void
    {
        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTestWithAuth($I->getAnonymousUser());

        $accessCheckPages->assert();
    }
}
