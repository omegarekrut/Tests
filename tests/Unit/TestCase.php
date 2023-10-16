<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase as UnitTestCase;
use Tests\Traits\FakerFactoryTrait;
use Tests\Traits\FileSystemTrait;
use Tests\Traits\UserGeneratorTrait;

abstract class TestCase extends UnitTestCase
{
    use FakerFactoryTrait;
    use FileSystemTrait;
    use UserGeneratorTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }
}
