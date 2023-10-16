<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\CreateWaterCommand;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use Ramsey\Uuid\Uuid;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class CreateWaterHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $command = new CreateWaterCommand(Uuid::uuid4(), 'Волга', WaterType::river());

        $this->getCommandBus()->handle($command);

        $waterRepository = $this->getEntityManager()->getRepository(Water::class);
        /** @var Water $water */
        $water = $waterRepository->find($command->getId());

        $this->assertEquals($command->getId(), $water->getId());
        $this->assertEquals($command->getName(), $water->getName());
        $this->assertEquals($command->getType(), $water->getType());
    }
}
