<?php

namespace Tests\Acceptance\Admin\Tackle;

use Codeception\Util\HttpCode;
use Tester;

class TackleBrandCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/tackle-brand/');
    }

    public function seeTackleBrandList(Tester $I): void
    {
        $I->see('Список брендов');
    }

    public function createTackleBrand(Tester $I): void
    {
        $I->click('Добавить бренд');
        $I->see('Добавление бренда');

        $title = $I->getFaker()->unique()->realText(50, 1);
        $I->fillField('tackle_brand[title]', $title);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Бренд успешно сохранен.');
        $I->seeInDatabase('tackle_brands', [
            'title' => $title,
        ]);
    }

    /**
     * @depends createTackleBrand
     */
    public function editTackleBrand(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование бренда');

        $newTitle = $I->getFaker()->unique()->realText(50, 1);
        $I->fillField('tackle_brand[title]', $newTitle);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Бренд успешно обновлен.');
        $I->amOnPage('/admin/tackle-brand/');
        $I->see($newTitle);
    }

    /**
     * @depends editTackleBrand
     */
    public function deleteTackelBrand(Tester $I): void
    {
        $firstId = (int) $I->grabTextFrom('td');
        $I->amOnPage(sprintf('/admin/tackle-brand/%s/delete/', $firstId));
        $I->amOnPage('/admin/tackles/');
        $I->dontSeeInSource(">$firstId<");
    }
}
