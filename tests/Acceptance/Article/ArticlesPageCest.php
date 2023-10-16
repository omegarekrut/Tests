<?php

namespace Tests\Acceptance\Article;

use Codeception\Util\HttpCode;
use PHPUnit\Framework\SkippedTestError;
use Tester;
use Tests\Acceptance\Traits\ComplainTrait;

class ArticlesPageCest
{
    use ComplainTrait;

    public function _before(Tester $I): void
    {
        $I->amOnPage('/articles/');
    }

    public function seeArticleList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Записи', 'h1');
    }

    public function seeArticleCategory(Tester $I): void
    {
        $I->click('.rubrics-list a');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeArticle(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function complainOnArticleAsNotBannedUser(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());
        $I->click('a.articleFS__content__link');
        $I->click('Сообщить о нарушении');

        $message = $this->fillComplainingFormAndSubmit($I);

        $I->seeAlert('success', 'Сообщение отправлено администрации сайта. Спасибо за вашу помощь!');

        $I->assertStringContainsString($message, $I->loadLastEmailMessage());
    }

    public function pagination(Tester $I): void
    {
        $I->click('.pagination a');
        $I->see('Страница 2');
        $I->see('Страница 2', 'title');
        $I->seeLink('Предыдущая', '/articles/');
    }

    public function seeArticleFirstPage(Tester $I): void
    {
        $I->amOnPage('/articles/page1');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function articleContentsIsGenerated(Tester $I): void
    {
        $articleId = $I->findArticleWithContentsGenerationPossibleId();

        if ($articleId === null) {
            throw new SkippedTestError('Не найдена статья, подходящая под условия формирования оглавления!');
        }

        $I->amOnPage(sprintf('/articles/view/%d/', $articleId));
        $I->seeInSource('<h2>Содержание</h2>');
    }

    /**
     * @inheritdoc
     */
    protected function grabRecordIds(Tester $I): array
    {
        return array_map(function ($item) {
            $match = [];
            if (preg_match('/\/view\/([\d]+)\//i', $item, $match)) {
                return (int) $match[1];
            }

            return 0;
        }, $I->grabMultiple('.record__info a.record__info-link', 'href'));
    }
}
