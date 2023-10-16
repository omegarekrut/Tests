<?php

namespace Tests\Unit\Helper;

use Twig\Environment;

trait TwigEnvironmentTrait
{
    private function mockTwigEnvironment(string $viewFile, array $arguments = [], $renderResult = ''): Environment
    {
        $environment = $this->createMock(Environment::class);

        $environment->expects($this->once())
            ->method('render')
            ->with($viewFile, $arguments)
            ->willReturn($renderResult);

        return $environment;
    }
}
