<?php

namespace Tests\Acceptance\Admin\SemanticLink;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group semantic_link
 */
class SemanticLinkStatisticCest
{
    public function _before(Tester $I): void
    {
        $I->authAs($I->findAdmin());
        $I->amOnPage('/admin/semantic-link/');
    }

    public function seeSemanticLinkStatistic(Tester $I): void
    {
        $I->click('//a[@title="Просмотреть"]');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see('Статистика семантической ссылки');
    }
}
