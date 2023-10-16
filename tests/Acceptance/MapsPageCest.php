<?php

namespace Tests\Acceptance;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Tester;

/**
 * @group record
 * @group map
 */
class MapsPageCest
{
    private $ajaxMarker;

    public function _before(Tester $I): void
    {
        $I->amOnPage('/maps/');
    }

    /**
     * @group seo
     */
    public function seeMapPointList(Tester $I): void
    {
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Рыболовные карты', 'h1');
    }

    public function  seeMapPoint(Tester $I): void
    {
        $I->click('a.articleFS__content__link');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function pagination(Tester $I): void
    {
        $I->click('.pagination a');
        $I->see('Страница 2');
        $I->see('Страница 2', 'title');
        $I->seeLink('Предыдущая', '/maps/');
    }

    public function seeMapsFirstPage(Tester $I): void
    {
        $I->amOnPage('/maps/page1');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * @dataprovider acceptHeaders
     */
    public function ajaxMarkersInformationForMapPoints(Tester $I, Example $config): void
    {
        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Accept', $config['accept']);
        $I->sendAjaxGetRequest('/maps/ajax_markers');
        $I->seeResponseCodeIs(HttpCode::OK);

        $answer = json_decode($I->grabResponse());
        $I->assertNotEmpty($answer);

        $this->ajaxMarker = $this->ajaxMarker ?: current($answer);
    }

    protected function acceptHeaders(): array
    {
        return [
            ['accept' => 'application/json, text/javascript, */*; q=0.01'],
            ['accept' => 'application/json, text/javascript, */*'],
        ];
    }

    /**
     * @depends ajaxMarkersInformationForMapPoints
     */
    public function ajaxMarkerInformationForMapPoint(Tester $I): void
    {
        $I->haveHttpHeader('Accept', '*/*');
        $I->sendAjaxPostRequest('/maps/ajax_marker_info/'.$this->ajaxMarker->id.'/');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
