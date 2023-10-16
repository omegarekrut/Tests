<?php

namespace Tests\Acceptance\Admin\Page;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group page
 */
class PagesCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/page/');
    }

    public function seePageList(Tester $I): void
    {
        $I->see('Список страниц');
    }

    public function createPage(Tester $I): void
    {
        $user = $I->findAdmin();

        $I->click('Добавить страницу');
        $I->see('Добавление страницы');

        $I->fillField('page[slug]', 'about');
        $I->fillField('page[title]', 'New about');
        $I->fillField('page[text]', 'About text');
        $I->click('Сохранить');
        $I->see('Страница с выбранным url \'about\' уже существует.');

        $slug = $I->getFaker()->slug;
        $I->fillField('page[slug]', $slug);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Страница успешно сохранена.');
        $I->seeInDatabase('pages', [
            'slug' => $slug,
            'title' => 'New about',
            'text' => 'About text',
            'username' => $user->username,
            'user_id' => $user->id,
        ]);
    }

    /**
     * @depends createPage
     */
    public function viewPageInPublic(Tester $I): void
    {
        $I->click('//a[@title="Просмотреть"]');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @depends viewPageInPublic
     */
    public function editPage(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование страницы');

        $newTitle = $I->getFaker()->realText(50, 1);
        $I->fillField('page[title]', $newTitle);
        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Страница успешно обновлена.');
        $I->amOnPage('/admin/page/');
        $I->see($newTitle);
    }

    /**
     * @depends editPage
     */
    public function deletePage(Tester $I): void
    {
        $firstId = (int) $I->grabTextFrom('td');
        $I->amOnPage(sprintf('/admin/page/%s/delete/', $firstId));
        $I->amOnPage('/admin/page/');
        $I->dontSeeInSource(">$firstId<");
    }
}
