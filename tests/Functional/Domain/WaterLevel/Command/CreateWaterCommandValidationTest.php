<?php

namespace Tests\Functional\Domain\WaterLevel\Command;

use App\Domain\WaterLevel\Command\CreateWaterCommand;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use Ramsey\Uuid\Uuid;
use Tests\Functional\ValidationTestCase;

/**
 * @group water-level
 */
class CreateWaterCommandValidationTest extends ValidationTestCase
{
    public function testEmptyName(): void
    {
        $command = new CreateWaterCommand(
            Uuid::uuid4(),
            '',
            WaterType::river()
        );

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('name', 'Это поле обязательно для заполнения.');
    }
}
