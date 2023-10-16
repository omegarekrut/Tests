<?php

namespace Tests\Acceptance\Admin\Article;

use App\Domain\Category\Entity\Category;
use Codeception\Util\HttpCode;
use Tester;

/**
 * @group article
 */
class ArticleCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/articles/');
    }

    public function seeArticleList(Tester $I): void
    {
        $I->see('Список записей');
    }

    public function createArticle(Tester $I): void
    {
        $I->click('Добавить запись');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Добавление записи');

        $title = $I->getFaker()->realText(50, 1);
        $text = $I->getFaker()->realText(150, 1);
        $priority = $I->getFaker()->randomDigitNotNull();
        $categoryId = $this->getArticleCategoryId($I);

        $I->selectOption('article[category]', $categoryId);
        $I->fillField('article[title]', $title);
        $I->fillField('article[text]', $text);
        $I->fillField('article[priority]', $priority);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись успешно добавлена.');

        $I->amOnPage('/admin/articles/');
        $I->see($title);
        $I->see($text);
        $I->see((string) $priority);
    }

    public function editArticle(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование записи');

        $title = $I->getFaker()->realText(50, 1);
        $preview = $I->getFaker()->realText(150, 1);
        $text = $I->getFaker()->realText(150, 1);
        $priority = $I->getFaker()->randomDigitNotNull();
        $categoryId = $this->getArticleCategoryId($I);

        $I->selectOption('article[category]', $categoryId);
        $I->fillField('article[title]', $title);
        $I->fillField('article[preview]', $preview);
        $I->fillField('article[text]', $text);
        $I->fillField('article[priority]', $priority);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись успешно обновлена.');

        $I->amOnPage('/admin/articles/');

        $I->see($title);
        $I->see($text);
        $I->see((string) $priority);
    }

    public function hideArticle(Tester $I): void
    {
        $firstId = (int) $I->grabTextFrom('td');

        $I->amOnPage(sprintf('/admin/articles/%d/hide/', $firstId));
        $I->amOnPage('/admin/articles/');
        $I->dontSeeInSource(">$firstId<");
    }

    private function getArticleCategoryId(Tester $I): int
    {
        $rootArticleCategoryId = $I->grabFromDatabase('categories', 'id', [
            'url_title' => Category::ROOT_ARTICLES_SLUG,
        ]);

        $categoryIds = $I->grabColumnFromDatabase('categories', 'id', [
            'parent_id' => $rootArticleCategoryId,
        ]);

        $index = rand(0, count($categoryIds) - 1);

        return $categoryIds[$index];
    }
}
