<?php

namespace Tests\Acceptance\Admin\Statistic;

use Codeception\Util\HttpCode;
use Tester;

class ArticleStatisticCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/statistic/new-articles/');
    }

    public function seeNewArticlesStatistic(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Статистика: новые статьи');
    }

    public function seeNewArticlesStatisticNoDataMessage(Tester $I): void
    {
        $I->fillField('#statistic_periodFrom', '2000-01-01');
        $I->fillField('#statistic_periodTo', '2000-01-31');
        $I->click('Показать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Отсутствуют данные для построения графика.');
    }
}
