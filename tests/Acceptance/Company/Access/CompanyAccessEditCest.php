<?php

namespace Tests\Acceptance\Company\Access;

use Page\AccessCheckPages;
use Tester;

class CompanyAccessEditCest
{
    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    private array $pages = [
        '/companies/%s/%s/edit/basic/',
        '/companies/%s/%s/edit/contacts/',
        '/companies/%s/%s/edit/images/',
        '/companies/%s/%s/edit/description/',
        '/companies/%s/%s/edit/social-networks/',
    ];

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );

        array_walk($this->pages, fn (&$page) => $page = sprintf($page, $this->companySlug, $this->companyShortUuid));
    }

    public function canSeeAccessAllowed(Tester $I): void
    {
        $isAllowed = function (Tester $I) {
            $I->seeInSource('Компании');
        };
        $companyOwner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_ALLOWED);
        $accessCheckPages
            ->addTest($companyOwner, $isAllowed, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
            ->addTest($I->findAdmin(), $isAllowed, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure())
        ;

        $accessCheckPages->assert();
    }

    public function canSeeAccessDeny(Tester $I): void
    {
        $isDenied = function (Tester $I) {
            $I->dontSeeInSource('Компании');
        };
        $companyOwner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);
        $userNotCompanyOwner = $I->findAnotherUserInGroup($companyOwner);

        $accessCheckPages = new AccessCheckPages($I, $this->pages, AccessCheckPages::STRATEGY_FORBIDDEN);
        $accessCheckPages
            ->addTest($userNotCompanyOwner, $isDenied, $accessCheckPages->getLoginClosure(), $accessCheckPages->getLogoutClosure());

        $accessCheckPages->assert();
    }
}
