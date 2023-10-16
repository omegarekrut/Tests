<?php

namespace Tests\Acceptance;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Tester;

/**
 * @group page
 * @group static-page
 */
class StaticPagesCest
{
    /**
     * @dataProvider getImportantPagesFromDatabase
     */
    public function showImportantFromDatabase(Tester $I, Example $example): void
    {
        $I->amOnPage($example['url']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($example['title'], 'h1');
    }

    public function getImportantPagesFromDatabase(): array
    {
        return [
            'contacts' => [
                'url' => '/contacts/',
                'title' => 'Контакты',
            ],
            'souv' => [
                'url' => '/pages/souv/',
                'title' => 'Сувениры FishingSib: кепки, наклейки на машины, флаги',
            ],
            'about' => [
                'url' => '/about/',
                'title' => 'О сайте Новосибирских рыбаков',
            ],
            'rules' => [
                'url' => '/rules/',
                'title' => 'Правила пользования сайтом',
            ],
            'search' => [
                'url' => '/search/',
                'title' => 'Поиск по сайту',
            ],
            'business-account' => [
                'url' => '/business-account/',
                'title' => 'Корпоративный аккаунт на FishingSib: новые клиенты для вашего бизнеса',
            ],
            'business-faq' => [
                'url' => '/business-faq/',
                'title' => 'Правила ведения бизнесс-аккаунта на FishingSib',
            ],
        ];
    }
}
