<?php

namespace Tests\Acceptance\Video;

use App\Domain\Region\Entity\Region;
use Codeception\Util\HttpCode;
use Tester;

class VideoCrudPageCest
{
    public function createVideoAsModerator(Tester $I): void
    {
        $I->authAs($I->findModerator());
        $I->amOnPage('/video/');
        $I->click('.add-record');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Добавить видео');

        $I->fillField('video[videoUrl]', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $I->click('Далее');

        $description = $I->getFaker()->realText(200);
        $categoryId = $I->grabRandomSubCategoryIdBySlug('video');
        $regionId = Region::OTHER_REGION_ID;

        $I->fillField('video[description]', $description);
        $I->fillField('video[category]', $categoryId);
        $I->fillField('video[regionId]', $regionId);

        $I->click('Опубликовать');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($description);
    }

    public function editVideoAsNotBannedUser(Tester $I): void
    {
        $videoId = $I->grabActiveVideoIdWithImageCreatedByNotBannedUser();
        $user = $I->grabUserByRecordId($videoId);

        $I->authAs($user);

        $I->amOnPage(sprintf('/video/edit/%d/', $videoId));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Редактировать видео');

        $categoryId = $I->grabRandomSubCategoryIdBySlug('video');
        $title = $I->getFaker()->realText(100);
        $description = $I->getFaker()->realText(200);
        $regionId = Region::OTHER_REGION_ID;

        $I->fillField('video[category]', $categoryId);
        $I->fillField('video[title]', $title);
        $I->fillField('video[description]', $description);
        $I->fillField('video[regionId]', $regionId);

        $I->click('Сохранить');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($title);
        $I->see($description);
    }

    public function hideVideoAsModerator(Tester $I): void
    {
        $user = $I->findModerator();
        $I->authAs($user);

        $videoId = $I->grabActiveVideoIdWithImageCreatedByNotBannedUser();
        $I->amOnPage(sprintf('/video/hide/%d/', $videoId));

        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
