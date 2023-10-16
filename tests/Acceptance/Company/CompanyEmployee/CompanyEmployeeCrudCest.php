<?php

namespace Tests\Acceptance\Company\CompanyEmployee;

use Tester;

class CompanyEmployeeCrudCest
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

    public function seeEmployeesListAsCompanyOwner(Tester $I): void
    {
        $companyOwner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($companyOwner);
        $I->amOnPage(sprintf('/companies/%s/%s/employees/', $this->companySlug, $this->companyShortUuid));

        $I->seeInSource('Управление сотрудниками компании');
    }

    public function testAddEmployeeAsCompanyOwner(Tester $I): void
    {
        $newEmployee = $I->findNotBannedUser();
        $companyOwner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($companyOwner);
        $I->amOnPage(sprintf('/companies/%s/%s/employees/add', $this->companySlug, $this->companyShortUuid));

        $I->fillField('company_employee[userLoginOrEmail]', $newEmployee->email);
        $I->click('Сохранить');

        $I->seeAlert('success', 'Добавлен новый сотрудник.');
    }

    public function testDeleteEmployeeAsCompanyOwner(Tester $I): void
    {
        $companyOwner = $I->findUserByCriteria(['userId' => $this->userOwnerId]);

        $I->authAs($companyOwner);
        $I->amOnPage(sprintf('/companies/%s/%s/employees/', $this->companySlug, $this->companyShortUuid));

        $I->click('a.iconFS--close');

        $I->seeAlert('success', 'Сотрудник был удален из компании.');
    }
}
