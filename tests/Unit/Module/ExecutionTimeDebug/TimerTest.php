<?php

namespace Tests\Unit\Module\ExecutionTimeDebug;

use App\Module\ExecutionTimeDebug\Timer;
use Tests\Unit\TestCase;

final class TimerTest extends TestCase
{
    public function testCreatedTimerMustBeStopped(): void
    {
        $timer = new Timer();

        $this->assertTrue($timer->isRunning());
    }

    public function testCanStopTimer(): void
    {
        $timer = new Timer();
        $timer->stop();

        $this->assertFalse($timer->isRunning());
    }

    public function testCanGettingTime(): void
    {
        $timer = new Timer();
        sleep(1);
        $timer->stop();

        $this->assertEquals(1, (int) $timer->getTime());
    }

    public function testSeeExceptionIfTryToStopNotRunningTimer(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Timer is not running.');

        $timer = new Timer();
        $timer->stop()->stop();
    }
}
