<?php

namespace Tests\Acceptance\Article;

use Codeception\Util\HttpCode;
use Tester;

class ArticleCrudPageCest
{
    public function createArticleAsNotBannedUser(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());

        $I->amOnPage('/articles/create/');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->fillField('article[category]', $I->grabSubCategoryIdBySlug('articles'));
        $I->fillField('article[title]', $I->getFaker()->realText(50));
        $I->fillField('article[text]', $I->getFaker()->realText(50));
        $I->click('Опубликовать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeAlert('success', 'Запись успешно добавлена.');
    }

    public function editArticleAsNotBannedUser(Tester $I): void
    {
        $articleId = $I->grabActiveRecordIdByTypeCreatedNotBannedUser('article');
        $user = $I->grabUserByRecordId($articleId);

        $I->authAs($user);

        $I->amOnPage(sprintf('/articles/%d/edit/', $articleId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Редактировать запись');

        $categoryId = $I->grabSubCategoryIdBySlug('articles');
        $title = $I->getFaker()->realText(100);
        $text = $I->getFaker()->realText(200);

        $I->fillField('article[category]', $categoryId);
        $I->fillField('article[title]', $title);
        $I->fillField('article[text]', $text);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($title);
        $I->see($text);
    }
}
