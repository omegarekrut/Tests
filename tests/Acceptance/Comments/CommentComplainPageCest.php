<?php

namespace Tests\Acceptance\Comments;

use Tester;
use Tests\Acceptance\Traits\ComplainTrait;

class CommentComplainPageCest
{
    use ComplainTrait;

    public function complainOnTidings(Tester $I): void
    {
        $I->authAs($I->findNotBannedUser());

        $I->amOnPage('/tidings/');
        $I->click('a.articleFS__content__link');

        $I->click('Сообщить о нарушении');

        $message = $this->fillComplainingFormAndSubmit($I);

        $I->seeAlert('success', 'Сообщение отправлено администрации сайта. Спасибо за вашу помощь!');
        $I->assertStringContainsString($message, $I->loadLastEmailMessage());
    }
}
