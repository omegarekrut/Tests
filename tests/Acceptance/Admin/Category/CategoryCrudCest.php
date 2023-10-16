<?php

namespace Tests\Acceptance\Admin\Category;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group category
 */
class CategoryCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/categories/');
    }

    public function seeCategoryList(Tester $I): void
    {
        $I->see('Список категорий');
    }

    public function createCategory(Tester $I): void
    {
        $I->click('Добавить категорию');
        $I->see('Добавление категории');

        $categoryTitle = $I->getFaker()->word;
        $categorySlug = $I->getFaker()->slug;
        $categoryDescription = $I->getFaker()->text(100);
        $parentCategoryId = $I->getFaker()->randomElement($I->grabAllCategoryIds());

        $I->selectOption('#category_parentCategory', $parentCategoryId);
        $I->fillField('category[title]', $categoryTitle);
        $I->fillField('category[slug]', $categorySlug);
        $I->fillField('category[description]', $categoryDescription);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Категория успешно добавлена');

        $I->amOnPage('/admin/categories/');
        $I->see($categoryTitle);
    }

    public function updateCategory(Tester $I): void
    {
        $allRootCategoryIds = $I->grabAllRootCategoryIds();

        $nonRootCategoryIds = $I->grabNonRootCategoryIds();
        $idOfCategoryToUpdate = $I->getFaker()->randomElement($nonRootCategoryIds);

        $I->amOnPage(sprintf('/admin/categories/%s/update', $idOfCategoryToUpdate));
        $I->see('Редактирование категории');

        $categoryTitle = $I->getFaker()->word;

        $I->fillField('category[title]', $categoryTitle);
        $I->selectOption('#category_parentCategory', $I->getFaker()->randomElement($allRootCategoryIds));
        $I->fillField('category[slug]', $I->getFaker()->slug);
        $I->fillField('category[description]', $I->getFaker()->text(100));

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Категория успешно обновлена');

        $I->amOnPage('/admin/categories/');
        $I->see($categoryTitle);
    }

    public function deleteNotEmptyCategory(Tester $I): void
    {
        $I->amOnPage('/admin/categories/');

        $notEmptyCategoryIds = $I->grabNotEmptyCategoryIds();
        $idOfCategoryToDelete = $I->getFaker()->randomElement($notEmptyCategoryIds);

        $deleteLink = sprintf('/admin/categories/%d/delete/', $idOfCategoryToDelete);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest($deleteLink);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function deleteDeletableCategory(Tester $I): void
    {
        $I->amOnPage('/admin/categories/');

        $deletableCategoryIds = $I->grabDeletableCategoryIds();
        $idOfCategoryToDelete = $I->getFaker()->randomElement($deletableCategoryIds);

        $deleteLink = sprintf('/admin/categories/%d/delete/', $idOfCategoryToDelete);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest($deleteLink);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInSource(">$idOfCategoryToDelete<");
    }
}
