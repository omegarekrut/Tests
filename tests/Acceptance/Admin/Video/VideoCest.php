<?php

namespace Tests\Acceptance\Admin\Video;

use Codeception\Scenario;
use Tester;

/**
 * @group video
 *
 * @todo updating cannot be tested because image form not supports php browser (only selenium)
 * test required new image form (uploader) with new protocol
 *
 * @see https://hunting.atlassian.net/browse/FS-1485
 */
class VideoCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/video/');
    }

    public function seeVideoList(Tester $I): void
    {
        $I->see('Видео о рыбалке');
    }

    public function hideVideo(Tester $I, Scenario $scenario): void
    {
        $firstId = (int) $I->grabTextFrom('td');
        if (empty($firstId)) {
            $scenario->skip('Not found video ID in html');
        }
        $I->amOnPage(sprintf('/admin/video/%d/hide/', $firstId));
        $I->amOnPage('/admin/video/');
        $I->dontSeeInSource(">$firstId<");
    }
}
