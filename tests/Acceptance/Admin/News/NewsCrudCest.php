<?php

namespace Tests\Acceptance\Admin\News;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group news
 *
 * @todo creating and updating cannot be tested because image form not supports php browser (only selenium)
 * test required new image form (uploader) with new protocol
 *
 * @see https://hunting.atlassian.net/browse/FS-1485
 */
class NewsCrudCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/news/');
    }

    public function seeNewsList(Tester $I): void
    {
        $I->see('Список новостей');
    }

    public function seeDeferredNewsInNewsList(Tester $I): void
    {
        $I->see('Deferred news title');
    }

    public function createNews(Tester $I): void
    {
        $I->click('Добавить новость');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Добавление новости');
    }

    public function editNews(Tester $I): void
    {
        $I->click('//a[@title="Изменить"]');
        $I->see('Редактирование новости');
    }
}
