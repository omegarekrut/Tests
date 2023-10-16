<?php

namespace Tests\Acceptance\Traits;

use Tester;

trait ComplainTrait
{
    protected function fillComplainingFormAndSubmit(Tester $I): string
    {
        $complainMessage = bin2hex(random_bytes(15));

        $I->fillField('textarea[id="complaint_text"]', $complainMessage);
        $I->click('Сообщить');

        return $complainMessage;
    }
}
