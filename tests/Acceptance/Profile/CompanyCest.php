<?php

namespace Tests\Acceptance\Profile;

use Tester;
use Tests\Support\TransferObject\User;

class CompanyCest
{
    private const COMPANY_LABEL = 'Компании';

    public function seeUserCompaniesAsCommonUser(Tester $I)
    {
        $commonUser = $I->findNotBannedUser();
        $userWithCompanies = $I->findUserWithCompanies();

        $I->authAs($commonUser);
        $I->amOnPage($this->getUrlToProfilePageForUser($userWithCompanies));

        $I->see(self::COMPANY_LABEL);
    }

    private function getUrlToProfilePageForUser(User $userWithCompanies): string
    {
        return sprintf('/users/profile/%d', $userWithCompanies->id);
    }
}
