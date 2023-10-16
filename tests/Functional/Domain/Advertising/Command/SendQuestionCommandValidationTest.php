<?php

namespace Tests\Functional\Domain\Seo\Command;

use App\Domain\Advertising\Command\SendQuestionCommand;
use Tests\Functional\ValidationTestCase;

/**
 * @group advertising
 */
class SendQuestionCommandValidationTest extends ValidationTestCase
{
    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid(new SendQuestionCommand(), ['userName', 'userEmail', 'message'], null, 'Значение не должно быть пустым.');
    }
}
