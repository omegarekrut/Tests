<?php

namespace Tests\Unit\Module\ExecutionTimeDebug;

use App\Module\ExecutionTimeDebug\ExecutionTimeViewInjector;
use Tests\Unit\TestCase;

final class ExecutionTimeViewInjectorTest extends TestCase
{
    public function testInjectingTimeViewIfBodyCloseTagExists(): void
    {
        $executionTimeViewInjector = new ExecutionTimeViewInjector();

        $html = '<html><body></body></html>';
        $result = $executionTimeViewInjector->tryInjectIntoHtml(2.5, $html);

        $this->assertStringContainsString('<!-- Time: 2.5 -->', $result);
    }

    public function testInjectedTimeShouldBeRounded(): void
    {
        $executionTimeViewInjector = new ExecutionTimeViewInjector();

        $html = '<html><body></body></html>';
        $result = $executionTimeViewInjector->tryInjectIntoHtml(2.5678, $html);

        $this->assertStringContainsString('<!-- Time: 2.568 -->', $result);
    }

    public function testNotInjectingTimeViewIfBodyCloseTagDoesntExist(): void
    {
        $executionTimeViewInjector = new ExecutionTimeViewInjector();

        $html = '<html>No body</html>';
        $result = $executionTimeViewInjector->tryInjectIntoHtml(2.5, $html);

        $this->assertStringNotContainsString('<!-- Time: 2.5 -->', $result);
    }
}
