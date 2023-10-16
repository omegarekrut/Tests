<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Record\Common\Command\Viewing\UpdateViewCountCommand;
use Tests\Functional\ValidationTestCase;

class UpdateViewCountCommandValidationTest extends ValidationTestCase
{
    public function testRecordNotExists(): void
    {
        $command = new UpdateViewCountCommand(1);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('recordId', 'Запись не найдена.');
    }
}
