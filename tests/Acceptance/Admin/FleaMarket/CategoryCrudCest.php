<?php

namespace Tests\Acceptance\Admin\FleaMarket;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group flea-market-category
 */
class CategoryCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/flea-market/categories/');
    }

    public function seeFleaMarketCategoryList(Tester $I): void
    {
        $categorySlugs = $I->grabDeletableFleaMarketCategorySlugs();
        $I->see('Список категорий');

        foreach ($categorySlugs as $categorySlug) {
            $I->see($categorySlug);
        }
    }

    public function createFleaMarketCategory(Tester $I): void
    {
        $I->click('Добавить категорию');
        $I->see('Добавление категории');

        $categoryTitle = $I->getFaker()->word;
        $categorySlug = $I->getFaker()->slug;
        $parentCategoryId = $I->getFaker()->randomElement($I->grabAllFleaMarketCategoryIds());

        $I->selectOption('#category_parentCategory', $parentCategoryId);
        $I->fillField('category[title]', $categoryTitle);
        $I->fillField('category[slug]', $categorySlug);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Категория успешно добавлена');

        $I->amOnPage('/admin/flea-market/categories/');
        $I->see($categoryTitle);
    }

    public function updateFleaMarketCategory(Tester $I): void
    {
        $nonRootCategorySlugs = $I->grabNonRootFleaMarketCategorySlugs();
        $idOfCategoryToUpdate = $I->getFaker()->randomElement($nonRootCategorySlugs);

        $I->amOnPage(sprintf('/admin/flea-market/categories/%s/edit', $idOfCategoryToUpdate));
        $I->see('Редактирование категории');

        $categoryTitle = $I->getFaker()->word;

        $I->fillField('category[title]', $categoryTitle);
        $I->selectOption('#category_parentCategory', '');
        $I->fillField('category[slug]', $I->getFaker()->slug);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Категория успешно обновлена');

        $I->amOnPage('/admin/flea-market/categories/');
        $I->see($categoryTitle);
    }

    public function deleteNotEmptyFleaMarketCategory(Tester $I): void
    {
        $I->amOnPage('/admin/flea-market/categories/');

        $notEmptyCategoryIds = $I->grabNotEmptyFleaMarketCategorySlugs();
        $idOfCategoryToDelete = $I->getFaker()->randomElement($notEmptyCategoryIds);

        $deleteLink = sprintf('/admin/flea-market/categories/%s/delete/', $idOfCategoryToDelete);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest($deleteLink);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function deleteFleaMarketCategoryWithoutChildren(Tester $I): void
    {
        $I->amOnPage('/admin/flea-market/categories/');

        $deletableCategorySlugs = $I->grabDeletableFleaMarketCategorySlugs();
        $slugsOfCategoryToDelete = $I->getFaker()->randomElement($deletableCategorySlugs);

        $deleteLink = sprintf('/admin/flea-market/categories/%s/delete/', $slugsOfCategoryToDelete);

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest($deleteLink);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInSource(">$slugsOfCategoryToDelete<");
    }
}
