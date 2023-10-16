<?php

namespace Tests\Unit\Domain\Log\Entity\ValueObject;

use App\Domain\Log\Entity\ValueObject\LoggingAction;
use InvalidArgumentException;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggingActionTest extends TestCase
{
    public function testActionCanBeCreatedWithNameAndParameters(): void
    {
        $expectedName = 'some name';
        $expectedParameters = [
            'parameter' => 'value',
        ];

        $action = new LoggingAction($expectedName, $expectedParameters);

        $this->assertEquals($expectedName, $action->getName());
        $this->assertEquals($expectedParameters, $action->getParameters());
    }

    public function testActionCanBeCreatedWithNullParameterValueParameters(): void
    {
        $loggingAction = new LoggingAction('some name', ['parameter' => null]);

        $this->assertEquals(null, $loggingAction->getParameters()['parameter']);
    }

    public function testActionCantBeCreatedWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action name must be filled');

        new LoggingAction('', []);
    }

    public function testActionCantBeCreatedWithArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action parameters must be pairs of scalar (or null) values');

        new LoggingAction('some name', ['parameter' => ['array']]);
    }

    public function testActionCantBeCreatedWithObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action parameters must be pairs of scalar (or null) values');

        new LoggingAction('some name', ['parameter' => $this]);
    }

    public function testActionCantBeCreatedWithResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action parameters must be pairs of scalar (or null) values');

        new LoggingAction('some name', ['parameter' => tmpfile()]);
    }
}
