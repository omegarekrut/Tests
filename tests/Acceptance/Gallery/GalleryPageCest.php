<?php

namespace Tests\Acceptance\Gallery;

use Codeception\Util\HttpCode;
use Codeception\Util\Locator;
use Tester;
use Tests\Acceptance\Traits\ComplainTrait;

/**
 * @group record
 * @group gallery
 */
class GalleryPageCest
{
    use ComplainTrait;

    public function _before(Tester $I): void
    {
        $I->amOnPage('/gallery/');
    }

    /**
     * @group seo
     */
    public function seeGalleryList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Рыболовная фотогалерея', 'h1');
    }

    public function seeBestPhotosBy30Days(Tester $I): void
    {
        $I->click('Все лучшие за 30 дней', '.asideBlock.best-gallery-last-month');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seeBestPhotos(Tester $I): void
    {
        $I->click('Все лучшие', '.asideBlock.best-gallery');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seePhotoSubcategory(Tester $I): void
    {
        $I->click(Locator::firstElement('a.topic__link '));
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function seePhoto(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function previousAndNextPhoto(Tester $I): void
    {
        $this->seePhoto($I);
        $this->goToPreviousPageAndReturnBackByNavigation($I);
    }

    public function previousAndNextPhotoInBest(Tester $I): void
    {
        $this->seeBestPhotos($I);
        $I->click('a.articleFS__content__link');

        $this->goToPreviousPageAndReturnBackByNavigation($I);
    }

    public function previousAndNextPhotoInBest30Days(Tester $I): void
    {
        $this->seeBestPhotosBy30Days($I);
        $I->click('a.articleFS__content__link');

        $this->goToPreviousPageAndReturnBackByNavigation($I);
    }

    public function previousAndNextPhotoInCategory(Tester $I): void
    {
        $this->seeGalleryList($I);
        $I->click('a.articleFS__content__link');

        $this->goToPreviousPageAndReturnBackByNavigation($I);
    }

    public function complainOnPhotoAsNotBannedUser(Tester $I): void
    {
        $this->seePhoto($I);
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
        $I->seeLink('Предыдущая', '/gallery/');
    }

    public function seeGalleryFirstPage(Tester $I): void
    {
        $I->amOnPage('/gallery/page1');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * @param Tester $I
     */
    private function goToPreviousPageAndReturnBackByNavigation(Tester $I): void
    {
        $I->click('.gallery-photo-view__next-link');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->click('.gallery-photo-view__prev-link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
