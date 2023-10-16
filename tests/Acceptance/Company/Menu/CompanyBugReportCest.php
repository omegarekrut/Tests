<?php

namespace Tests\Acceptance\Company\Menu;

use Tester;

class CompanyBugReportCest
{
    private const REPORT_BUG_BUTTON_LABEL = 'Сообщить об ошибке';

    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );
    }

    public function seeButtonForReportBugAsOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($owner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));

        $I->seeInSource(self::REPORT_BUG_BUTTON_LABEL);
    }

    public function dontSeeButtonForReportBugAsNotOwnerCest(Tester $I): void
    {
        $owner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);
        $notOwner = $I->findAnotherUserInGroup($owner);

        $I->authAs($notOwner);
        $I->amOnPage(sprintf('/companies/%s/%s', $this->companySlug, $this->companyShortUuid));

        $I->dontSeeInSource(self::REPORT_BUG_BUTTON_LABEL);
    }
}
