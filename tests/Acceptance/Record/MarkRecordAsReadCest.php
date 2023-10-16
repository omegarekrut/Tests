<?php

namespace Tests\Acceptance\Record;

use Tester;

class MarkRecordAsReadCest
{
    public function markAsReadAs(Tester $I): void
    {
        $recordId = $I->grabActiveTidingsId();
        $oldViews = $I->grabColumnFromDatabase('records', 'views', [
            'id' => $recordId,
        ])[0];

        $I->sendPOST(sprintf('/records/%d/mark-as-read/', $recordId));
        $I->seeResponseCodeIsSuccessful();

        $I->canSeeInDatabase('records', [
            'id' => $recordId,
            'views' => $oldViews + 1,
        ]);
    }
}
