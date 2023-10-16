<?php

namespace Tests\Acceptance\Company\Menu;

use Tester;

class CompanyAskOwnershipCest
{
    private const BUTTON_LABEL = 'Я владелец этой компании';

    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );
    }

    public function dontSeeButtonForAskBecomeOwnerAsOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($owner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));
        $I->dontSeeInSource(self::BUTTON_LABEL);
    }

    public function seeButtonForAskBecomeOwnerAsNotOwner(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);
        $notOwner = $I->findAnotherUserInGroup($owner);

        $I->authAs($notOwner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));
        $I->seeInSource(self::BUTTON_LABEL);
    }
}
