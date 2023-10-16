<?php

namespace Tests\Acceptance\Admin\SemanticLink;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group semantic_link
 */
class SemanticLinkSortCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/semantic-link/');
    }

    public function seeSemanticLinkRulesList(Tester $I): void
    {
        $I->see('Семантические ссылки');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortSemanticLinkByUri(Tester $I): void
    {
        $I->amOnPage('/admin/semantic-link/');
        $I->click('Ссылка');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortSemanticLinkByText(Tester $I): void
    {
        $I->amOnPage('/admin/semantic-link/');
        $I->click('Текст');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function sortSemanticLinkByCount(Tester $I): void
    {
        $I->amOnPage('/admin/semantic-link/');
        $I->click('Количество активных ссылок');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
