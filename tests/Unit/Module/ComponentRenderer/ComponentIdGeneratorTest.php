<?php

namespace Tests\Unit\Module\ComponentRenderer;

use App\Module\ComponentRenderer\ComponentIdGenerator;
use Tests\Unit\TestCase;

class ComponentIdGeneratorTest extends TestCase
{
    public function testGenerateReturnEqualIdsForIdenticalComponents(): void
    {
        $componentIdGenerator = new ComponentIdGenerator();

        $firstComponentId = $componentIdGenerator->generate('SomeComponent', ['filed_1' => 'example', 'filed_2' => 123]);
        $secondComponentId = $componentIdGenerator->generate('SomeComponent', ['filed_2' => 123, 'filed_1' => 'example']);

        $this->assertEquals($firstComponentId, $secondComponentId);
    }
}
