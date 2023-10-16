<?php

namespace Tests\Acceptance\Company\Menu;

use Tester;

class CompanyWishCest
{
    private const WISH_LABEL = 'Оставить пожелание';

    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );
    }

    public function seeButtonForSendWishAsOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($owner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));

        $I->seeInSource(self::WISH_LABEL);
    }

    public function dontSeeButtonForSendWishAsNotOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);
        $notOwner = $I->findAnotherUserInGroup($owner);

        $I->authAs($notOwner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));

        $I->dontSeeInSource(self::WISH_LABEL);
    }
}
