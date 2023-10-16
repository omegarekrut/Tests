<?php

namespace Tests\Acceptance\Company\Menu;

use Tester;

class CompanyReportInaccuracyCest
{
    private const BUTTON_LABEL = 'Сообщить о неточности';

    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );
    }

    public function dontSeeButtonForReportInaccuracyAsOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($owner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));
        $I->dontSeeInSource(self::BUTTON_LABEL);
    }

    public function seeButtonForReportInaccuracyAsNotOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);
        $notOwner = $I->findAnotherUserInGroup($owner);

        $I->authAs($notOwner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));
        $I->seeInSource(self::BUTTON_LABEL);
    }
}
