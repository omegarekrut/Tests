<?php

namespace Tests\Acceptance\Admin\Company;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group company
 */
class RubricCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/rubric/');
    }

    public function seeRubricList(Tester $I): void
    {
        $I->see('Список рубрик');
    }

    public function createRubric(Tester $I): void
    {
        $I->click('Добавить рубрику');
        $I->see('Добавление рубрики');

        $rubricName = $I->getFaker()->word;
        $rubricSlug = $I->getFaker()->slug;
        $visibilityIndex = $I->getFaker()->randomNumber();

        $I->fillField('rubric[name]', $rubricName);
        $I->fillField('rubric[slug]', $rubricSlug);
        $I->fillField('rubric[priority]', $visibilityIndex);

        $I->click('Сохранить');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Рубрика успешно добавлена');
    }

    public function editRubric(Tester $I): void
    {
        $rubric = $I->findRubricIdAndNameWithoutCompany();
        $idRubric = $rubric['id'];

        $editLink = sprintf('/admin/rubric/%s/edit', $idRubric);

        $I->amOnPage($editLink);
        $I->see('Редактирование рубрики');

        $rubricName = $I->getFaker()->word;
        $rubricSlug = $I->getFaker()->slug;
        $visibilityIndex = $I->getFaker()->randomNumber();

        $I->fillField('rubric[name]', $rubricName);
        $I->fillField('rubric[slug]', $rubricSlug);
        $I->fillField('rubric[priority]', $visibilityIndex);

        $I->click('Сохранить');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Рубрика успешно обновлена.');
    }

    public function deleteRubric(Tester $I): void
    {
        $rubric = $I->findRubricIdAndNameWithoutCompany();
        $idRubric = $rubric['id'];
        $nameRubric = $rubric['name'];

        $I->amOnPage('/admin/rubric/');
        $I->see($nameRubric);

        $deleteLink = sprintf('/admin/rubric/%s/delete/', $idRubric);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest($deleteLink);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInSource(">$idRubric<");
    }
}
