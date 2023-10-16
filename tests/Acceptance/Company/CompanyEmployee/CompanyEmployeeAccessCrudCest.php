<?php

namespace Tests\Acceptance\Company\CompanyEmployee;

use Symfony\Component\HttpFoundation\Response;
use Tester;

class CompanyEmployeeAccessCrudCest
{
    private int $userOwnerId;
    private string $companySlug;
    private string $companyShortUuid;

    public function _before(Tester $I): void
    {
        [$this->userOwnerId, $this->companySlug, $this->companyShortUuid] = array_values(
            $I->grabPublicCompanyRequestParamsWithUserOwnerId()
        );
    }

    public function seeDeniedEmployeesListAsNotCompanyOwner(Tester $I): void
    {
        $notCompanyOwner = $I->grabUserByLogin('test');

        $I->authAs($notCompanyOwner);
        $I->amOnPage(sprintf('/companies/%s/%s/employees/', $this->companySlug, $this->companyShortUuid));

        $I->seeResponseCodeIs(Response::HTTP_FORBIDDEN);
    }

    public function seeDeniedAddEmployeeAsNotCompanyOwner(Tester $I): void
    {
        $notCompanyOwner = $I->grabUserByLogin('test');

        $I->authAs($notCompanyOwner);
        $I->amOnPage(sprintf('/companies/%s/%s/employees/add', $this->companySlug, $this->companyShortUuid));

        $I->seeResponseCodeIs(Response::HTTP_FORBIDDEN);
    }
}
