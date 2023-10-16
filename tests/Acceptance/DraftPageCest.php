<?php

namespace Tests\Acceptance;

use Codeception\Scenario;
use Codeception\Util\HttpCode;
use Tester;

/**
 * @group draft
 */
class DraftPageCest
{
    public function seeDraftAsGuest(Tester $I, Scenario $scenario): void
    {
        $draftId = $this->getLastDraftId($I);
        if (!$draftId) {
            $scenario->skip('Нет ни одного черновика в базе данных');
        }

        $draftTitle = $I->grabColumnFromDatabase('drafts', 'title', ['id' => $draftId]);
        $draftText = $I->grabColumnFromDatabase('drafts', 'text', ['id' => $draftId]);

        $I->amOnPage("/drafts/$draftId/");
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->see($draftTitle[0], 'h1');
        $I->seeInSource($draftText[0]);
    }

    private function getLastDraftId(Tester $I): int
    {
        $draftIds = $I->grabColumnFromDatabase('drafts', 'id');

        return (int) array_pop($draftIds);
    }
}
