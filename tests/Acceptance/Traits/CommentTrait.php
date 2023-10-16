<?php

namespace Tests\Acceptance\Traits;

use Tester;

trait CommentTrait
{
    protected function fillCommentCreationFormAndSubmit(Tester $I): string
    {
        $commentMessage = bin2hex(random_bytes(15));

        $I->fillField('form[name="create_comment"] textarea[name="create_comment[text]"]', $commentMessage);
        $I->submitForm('form[name="create_comment"]', []);

        return $commentMessage;
    }

    protected function fillCommentEditingFormAndSubmit(Tester $I): string
    {
        $commentMessage = bin2hex(random_bytes(15));

        $I->fillField('form[name="update_comment"] textarea[name="update_comment[text]"]', $commentMessage);
        $I->submitForm('form[name="update_comment"]', []);

        return $commentMessage;
    }
}
