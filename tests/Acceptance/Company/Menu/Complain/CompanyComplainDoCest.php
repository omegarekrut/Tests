<?php

namespace Tests\Acceptance\Company\Menu\Complain;

use Tester;

class CompanyComplainDoCest
{
    public function seeCanComplainToNotOwnedCompanyCest(Tester $I): void
    {
        $companyRequestParams = $I->grabNotOwnedCompanyRequestParams();

        $I->authAs($I->findNotBannedUser());

        $this->complaintToCompany($I, $companyRequestParams);
    }

    public function seeCanComplainToOwnedCompanyCest(Tester $I): void
    {
        $isPublic = true;
        $companyRequestParams = $I->grabPublicCompanyRequestParamsWithUserOwnerId();

        $companyOwner = $I->findUserByCriteria(['userId' => (int) $companyRequestParams['user_id']]);
        $notCompanyOwner = $I->findAnotherUserInGroup($companyOwner);

        $I->authAs($notCompanyOwner);

        $this->complaintToCompany($I, $companyRequestParams);
    }

    /**
     * @param string[] $companyRequestParams
     */
    private function complaintToCompany(Tester $I, array $companyRequestParams): void
    {
        $I->amOnPage(sprintf('/companies/%s/%s/', $companyRequestParams['slug'], $companyRequestParams['short_uuid']));
        $I->click('Пожаловаться');
        $I->fillField('complaint[text]', 'Жалоба');
        $I->click('Сообщить');
        $I->seeAlert('success', 'Сообщение отправлено администрации сайта. Спасибо за вашу помощь!');
    }
}
