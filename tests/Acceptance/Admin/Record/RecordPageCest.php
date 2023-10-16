<?php

namespace Tests\Acceptance\Admin\Record;

use Codeception\Util\HttpCode;
use Tester;

class RecordPageCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/record/');
    }

    public function seeRecordList(Tester $I): void
    {
        $I->see('Настройка записей');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function onlyReadComments(Tester $I): void
    {
        $I->click('Только для чтения', 'ul[aria-labelledby="comments-access-dropdown"]');

        $I->seeAlert('success', 'Комментарии теперь будут только для чтения');
    }

    public function disallowComments(Tester $I): void
    {
        $I->click('Скрывать', 'ul[aria-labelledby="comments-access-dropdown"]');

        $I->seeAlert('success', 'Комментарии успешно скрыты');
    }

    public function allowComments(Tester $I): void
    {
        $I->click('Показывать', 'ul[aria-labelledby="comments-access-dropdown"]');

        $I->seeAlert('success', 'Комментарии теперь будут отображаться');
    }

    public function updatePriority(Tester $I): void
    {
        $I->click('//a[@title="Изменить приоритет"]');
        $I->see('Изменение приоритета записи');

        $previousPriority = $I->grabTextFrom('//table/tbody/tr/td[4]');
        $currentUrl = $I->getCurrentUrl();

        $I->fillField('#record_priority_priority', 1);
        $I->click('Сохранить');

        $I->seeAlert('success', 'Приоритет записи изменен успешно.');

        $I->amOnPage($currentUrl);

        $I->fillField('#record_priority_priority', $previousPriority);
        $I->click('Сохранить');
    }
}
