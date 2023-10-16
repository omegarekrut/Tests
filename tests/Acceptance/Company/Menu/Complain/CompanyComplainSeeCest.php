<?php

namespace Tests\Acceptance\Company\Menu\Complain;

use Tester;

class CompanyComplainSeeCest
{
    private const BUTTON_LABEL = 'Пожаловаться';

    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );
    }

    public function dontSeeButtonForComplaintCompanyAsOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($owner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));
        $I->dontSeeInSource(self::BUTTON_LABEL);
    }

    public function seeButtonForComplaintCompanyAsNotOwner(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);
        $notOwner = $I->findAnotherUserInGroup($owner);

        $I->authAs($notOwner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));
        $I->seeInSource(self::BUTTON_LABEL);
    }
}
