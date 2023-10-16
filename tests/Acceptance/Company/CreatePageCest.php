<?php

namespace Tests\Acceptance\Company;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group company
 * @group company-create
 */
class CreatePageCest
{
    private $user;

    public function _before(Tester $I): void
    {
        $this->user = $I->findAdmin();
        $I->authAs($this->user);
    }

    public function seeCanCreateCompany(Tester $I): void
    {
        $this->createCompany($I);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Новая компания сохранена.');
    }

    public function seeBreadCrumbs(Tester $I): void
    {
        $I->amOnPage('companies/create');

        $breadCrumbsSectionClass = '.breadcrumbs';
        $I->see('Товары и услуги для рыбалки', $breadCrumbsSectionClass);
        $I->see('Главная', $breadCrumbsSectionClass);
    }

    public function seeCanCreateCompanyWithContactAddress(Tester $I)
    {
        $this->createCompany($I);
    }

    private function createCompany(Tester $I): void
    {
        $I->amOnPage('/companies/create/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Создание компании');

        $newRubric = $I->findRandomCompanyRubric();
        $newName = $I->getFaker()->company;
        $newScopeActivity = $I->getFaker()->catchPhrase;

        $I->selectOption('#company_basic_info_rubrics', $newRubric['id']);
        $I->fillField('company_basic_info[name]', $newName);
        $I->fillField('company_basic_info[scopeActivity]', $newScopeActivity);

        $I->click('Сохранить');
    }

}
