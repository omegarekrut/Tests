<?php

namespace Tests\Acceptance\Admin\Rss;

use Codeception\Util\HttpCode;
use Tester;

class RssModerationCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/');
        $I->click('Яндекс.Дзен');
    }

    public function addPageInZen(Tester $I): void
    {
        $I->click('Одобрить в ленту');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись разрешена к экспорту в RSS');
    }

    public function hidePageFromListForZen(Tester $I): void
    {
        $I->click('Скрыть');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись запрещена к экспорту в RSS');
    }

    public function seeAlreadyModeratedRecords(Tester $I): void
    {
        $I->click('Последние записи прошедшие модерацию');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Записи за последние 4 дня, прошедшие модерацию.');
    }
}
