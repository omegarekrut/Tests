<?php

namespace Tests\Functional\Domain\Log\Command;

use App\Domain\Log\Command\LogSpammerDetectionCommand;
use App\Domain\User\Entity\User;
use Tests\Functional\ValidationTestCase;

/**
 * @group log
 * @group spam-detection
 */
class LogSpammerDetectionCommandValidationTest extends ValidationTestCase
{
    /** @var LogSpammerDetectionCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new LogSpammerDetectionCommand($this->createMock(User::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testReasonIsRequired(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('detectionReason', 'Значение не должно быть пустым.');
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->detectionReason = 'readon';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
