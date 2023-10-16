<?php

namespace Tests\Acceptance\Admin\Draft;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group draft
 */
class DraftCrudCest
{
    public function _before(\Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/draft/');
    }

    public function seeDraftsList(Tester $I): void
    {
        $I->see('Список черновиков');
    }

    public function createDraft(Tester $I): void
    {
        $I->click('Добавить черновик');
        $I->see('Добавление черновика');

        $title = 'New title created at '.time();
        $text = 'Draft text created  at '.time();
        $I->fillField('draft[title]', $title);
        $I->fillField('draft[text]', $text);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Черновик успешно сохранен.');
        $I->seeInDatabase('drafts', [
            'title' => $title,
            'text' => $text,
        ]);
    }

    /**
     * @depends createDraft
     */
    public function seeDraftInPublicFromAdminLink(Tester $I): void
    {
        $I->click('//a[@title="Просмотреть"]');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @depends createDraft
     */
    public function editDraft(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование черновика');

        $newTitle = $I->getFaker()->realText(50, 1);
        $I->fillField('draft[title]', $newTitle);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Черновик успешно обновлен.');
        $I->amOnPage('/admin/draft/');
        $I->see($newTitle);
    }

    /**
     * @depends createDraft
     */
    public function deleteDraft(Tester $I): void
    {
        $firstId = (int) $I->grabTextFrom('td');
        $I->amOnPage(sprintf('/admin/draft/%s/delete/', $firstId));
        $I->amOnPage('/admin/draft/');
        $I->dontSeeInSource(">$firstId<");
    }
}
