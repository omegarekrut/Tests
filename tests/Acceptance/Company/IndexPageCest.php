<?php

namespace Tests\Acceptance\Company;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group company
 */
class IndexPageCest
{
    private const INDEX_PAGE = '/companies/';

    public function _before(Tester $I): void
    {
        $I->amOnPage(self::INDEX_PAGE);
    }

    public function seeCompanyIndexPage(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Товары и услуги для рыбалки', 'h1');
    }

    public function seeCompanyInfoSelectorIndexPage(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Новости компаний', 'span');
        $I->see('Каталог', 'span');
    }

    public function seeCompany(Tester $I): void
    {
        $I->click('a.company-list-item__name');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeRubric(Tester $I): void
    {
        $I->click('a.contentFS__header__category');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeNextPageAfterClickNextPage(Tester $I): void
    {
        $I->click('.pagination .arrow--next a');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Товары и услуги для рыбалки. Страница 2', 'h1');
        $I->seeLink('Предыдущая', self::INDEX_PAGE);
    }

    public function dontSeeCompanyFirstPage(Tester $I): void
    {
        $I->amOnPage(self::INDEX_PAGE . 'page1/');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function seeDefaultCompanyLogoImage(Tester $I): void
    {
        $I->amOnPage(self::INDEX_PAGE);
        $this->seeImageWithSource($I, '/img/icon/business.svg');
    }

    private function seeImageWithSource(Tester $I, string $imageSource)
    {
        $I->seeElement('//img[@src="'.$imageSource.'"]');
    }
}
