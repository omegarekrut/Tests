<?php

namespace Tests\Acceptance\Company\CompanyMap;

use Codeception\Example;
use Codeception\Util\HttpCode;
use Tester;

class CompanyMapPageCest
{
    private array $rubricWithCompany;

    public function _before(Tester $I): void
    {
        $this->rubricWithCompany = $I->findRubricWithCompany();

        $I->amOnPage('/companies/');
    }

    /**
     * @dataprovider acceptHeaders
     */
    public function ajaxMarkersInformationForCompanyMapPoints(Tester $I, Example $config): void
    {
        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Accept', $config['accept']);
        $I->sendAjaxGetRequest('ajax-markers/');
        $I->seeResponseCodeIs(HttpCode::OK);

        $answer = json_decode($I->grabResponse());
        $I->assertNotEmpty($answer);
    }

    /**
     * @dataprovider acceptHeaders
     */
    public function ajaxMarkersInformationForCompanyMapPointsByRubric(Tester $I, Example $config): void
    {
        $I->setHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Accept', $config['accept']);
        $I->sendAjaxGetRequest('ajax-markers/rubrics/' . $this->rubricWithCompany['id']);
        $I->seeResponseCodeIs(HttpCode::OK);

        $answer = json_decode($I->grabResponse());
        $I->assertNotEmpty($answer);
    }

    private function acceptHeaders(): array
    {
        return [
            ['accept' => 'application/json, text/javascript, */*; q=0.01'],
            ['accept' => 'application/json, text/javascript, */*'],
        ];
    }
}
