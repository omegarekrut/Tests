<?php

namespace Tests\Acceptance\Admin\RecommendedRecord;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group recommended-record
 */
class RecommendedRecordCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/recommended-record/');
    }

    public function editRecommendedRecord(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование рекомендуемой новости');

        $priority = $I->getFaker()->randomDigitNotNull();

        $I->fillField('update_recommended_record[priority]', $priority);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Рекомендованная новость успешно обновлена.');

        $I->amOnPage('/admin/recommended-record/');

        $I->see((string) $priority);
    }
}
