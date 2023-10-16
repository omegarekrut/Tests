<?php

namespace Tests\Acceptance\Admin\FleaMarket;

use Codeception\Util\HttpCode;
use Tester;

class FleaMarketBrandCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/flea-market/brands/');
    }

    public function seeFleaMarketBrandList(Tester $I): void
    {
        $I->see('Список брендов');
    }

    public function createFleaMarketBrand(Tester $I): void
    {
        $I->click('Добавить бренд');
        $I->see('Добавление бренда');

        $brandTitle = $I->getFaker()->word;
        $brandDescription = $I->getFaker()->slug;

        $I->fillField('brand[title]', $brandTitle);
        $I->fillField('brand[description]', $brandDescription);

        $I->click('Создать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Бренд успешно добавлен');

        $I->amOnPage('/admin/flea-market/brands/');
        $I->see($brandTitle);
    }

    public function updateFleaMarketBrand(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование бренда');

        $brandTitle = $I->getFaker()->word;

        $I->fillField('brand[title]', $brandTitle);

        $I->click('Обновить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Бренд успешно обновлен');

        $I->amOnPage('/admin/flea-market/brands/');
        $I->see($brandTitle);
    }

    public function deleteFleaMarketBrand(Tester $I): void
    {
        $deleteLink = $I->grabAttributeFrom('span[data-js-action="ajax-delete"]', 'data-js-target');

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest($deleteLink);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInSource($deleteLink);
    }
}
