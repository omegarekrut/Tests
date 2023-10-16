<?php

namespace Tests\Acceptance\Admin\SemanticLink;

use Codeception\Util\HttpCode;
use Tester;

/**
 * @group semantic_link
 */
class SemanticLinkImportCest
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

    public function importSemanticLinkRule(Tester $I): void
    {
        $I->amOnPage('/admin/semantic-link/');
        $I->click('Импорт');
        $I->see('Импорт семантических ссылкок');

        $I->attachFile('import_semantic_link[file]', 'tempSemanticLinkImport.xlsx');

        $I->click('Сохранить');

        $I->seeAlert('success', 'Импорт семантических ссылок успешно запущен.');
    }

    /**
     * @depends importSemanticLinkRule
     */
    public function findSemanticLinkRule(Tester $I): void
    {
        $I->fillField('semantic_link_search[uri]', 'articles/view/86281/');
        $I->click('//button[@type="submit"]');
        $I->see('articles/view/86281/');
    }
}
