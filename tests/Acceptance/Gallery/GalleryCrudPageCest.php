<?php

namespace Tests\Acceptance\Gallery;

use App\Domain\Region\Entity\Region;
use Codeception\Util\HttpCode;
use Tester;

class GalleryCrudPageCest
{
    public function createGalleryAsNotBannedUser(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());

        $I->amOnPage('/gallery/create/');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function editGalleryAsNotBannedUser(Tester $I): void
    {
        $galleryId = $I->grabActiveRecordIdByTypeCreatedNotBannedUser('gallery');
        $user = $I->grabUserByRecordId($galleryId);

        $I->authAs($user);

        $I->amOnPage(sprintf('/gallery/%d/edit/', $galleryId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Редактировать фото');

        $categoryId = $I->grabSubCategoryIdBySlug('gallery');
        $title = $I->getFaker()->realText(100);
        $data = $I->getFaker()->realText(200);
        $regionId = Region::OTHER_REGION_ID;

        $I->fillField('gallery[category]', $categoryId);
        $I->fillField('gallery[title]', $title);
        $I->fillField('gallery[data]', $data);
        $I->fillField('gallery[regionId]', $regionId);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($title);
        $I->see($data);
    }
}
