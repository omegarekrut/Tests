<?php

namespace Tests\Acceptance\Video;

use Codeception\Util\HttpCode;
use Tester;
use Tests\Acceptance\Traits\ComplainTrait;

class VideoPageCest
{
    use ComplainTrait;

    public function _before(Tester $I): void
    {
        $I->amOnPage('/video/');
    }

    public function seeVideoPage(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Видео о рыбалке', 'h1');
    }

    public function seeVideo(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeSubcategory(Tester $I): void
    {
        $I->click('.rubrics-list a');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function complainOnVideoAsNotBannedUser(Tester $I): void
    {
        $this->seeVideo($I);
        $I->authAs($I->findNotBannedUser());

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
        $I->seeLink('Предыдущая', '/video/');
    }

    public function seeVideoFirstPage(Tester $I): void
    {
        $I->amOnPage('/video/page1');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function ajaxGetVideoInformation(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());

        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->sendAjaxPostRequest('/video/get-information/', ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $I->seeResponseCodeIs(HttpCode::OK);

        $answer = json_encode($I->grabResponse());
        $I->assertNotEmpty($answer);
    }
}
