<?php

namespace Tests\Acceptance\Tidings;

use Page\AccessCheckPages;
use Tester;

class TidingsAccessCest
{
    private $notBannedUser;
    private $tidingsId;

    public function _before(Tester $I): void
    {
        $this->notBannedUser = $I->findNotBannedUser();

        $this->tidingsId = $I->grabActiveTidingsId();
    }

    public function allowDeletePageAccessAsAdmin(Tester $I): void
    {
        $pages = [
            sprintf('/tidings/delete/%d/', $this->tidingsId),
        ];

        $accessCheckPages = new AccessCheckPages($I, $pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTestWithAuth($I->findAdmin());

        $accessCheckPages->assert();
    }

    public function allowDeletePageAccessAsModerator(Tester $I): void
    {
        $pages = [
            sprintf('/tidings/delete/%d/', $this->tidingsId),
        ];

        $accessCheckPages = new AccessCheckPages($I, $pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages->addTestWithAuth($I->findModerator());

        $accessCheckPages->assert();
    }

    public function denyDeletePageAccess(Tester $I): void
    {
        $pages = [
            sprintf('/tidings/delete/%d/', $this->tidingsId),
        ];

        $accessCheckPages = new AccessCheckPages($I, $pages, AccessCheckPages::STRATEGY_DENIED);
        $accessCheckPages->addTestWithAuth($this->notBannedUser);

        $accessCheckPages->assert();
    }
}
