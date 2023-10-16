<?php

namespace Tests\Acceptance\Admin\Statistic;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group statistic
 */
class GalleryStatisticCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/statistic/new-gallery/');
    }

    public function seeNewGalleryStatistic(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Статистика: новые фото');
    }

    public function seeNewGalleryStatisticNoDataMessage(Tester $I): void
    {
        $I->fillField('#statistic_periodFrom', '2000-01-01');
        $I->fillField('#statistic_periodTo', '2000-01-31');
        $I->click('Показать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Отсутствуют данные для построения графика.');
    }
}
