<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Record\Common\Command\UpdateRecord\UpdateRecordPriorityCommand;
use App\Domain\Record\Common\Entity\Record;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Functional\ValidationTestCase;

/**
 * @group record
 */
class UpdateRecordPriorityCommandValidationTest extends ValidationTestCase
{
    use ProphecyTrait;

    public function testToDoNothing(): void
    {
        $command = new UpdateRecordPriorityCommand($this->mockRecord());
        $command->priority = 0;

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testNotInteger(): void
    {
        $command = new UpdateRecordPriorityCommand($this->mockRecord());
        $this->assertOnlyFieldsAreInvalid(
            $command,
            ['priority'],
            $this->getFaker()->realText(10),
            'Значение не является числом.'
        );
    }

    public function testLessThanMinInteger(): void
    {
        $command = new UpdateRecordPriorityCommand($this->mockRecord());
        $this->assertOnlyFieldsAreInvalid($command, ['priority'], -1, 'Минимальное значение приоритета 0.');
    }

    private function mockRecord(): Record
    {
        $record = $this->prophesize(Record::class);

        return $record->reveal();
    }
}
