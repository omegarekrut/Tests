<?php

namespace Tests\Unit\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleBrand\Handler\UpdateTackleBrandHandler;
use App\Domain\Record\Tackle\Command\TackleBrand\UpdateTackleBrandCommand;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Repository\TackleBrandRepository;
use Tests\Unit\TestCase;

/**
 * @group tackle
 * @group tackle-brand
 */
class UpdateTackleBrandCommandHandlerTest extends TestCase
{
    public function testHandle()
    {
        $commandHandler = new UpdateTackleBrandHandler($this->getTackleBrandRepository());

        $commandHandler->handle($this->getCommand());
    }

    private function getTackleBrandRepository(): TackleBrandRepository
    {
        $repository = $this->createMock(TackleBrandRepository::class);

        $repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($entity) {
                $this->assertInstanceOf(TackleBrand::class, $entity);
                $this->assertEquals(1, $entity->getId());
            });

        return $repository;
    }

    private function getCommand(): UpdateTackleBrandCommand
    {
        $command = new UpdateTackleBrandCommand($this->getTackleBrandMock());

        return $command;
    }

    private function getTackleBrandMock(): TackleBrand
    {
        $tackleBrand = $this->createMock(TackleBrand::class);
        $tackleBrand
            ->expects($this->once())
            ->method('setTitle');

        $tackleBrand->method('getId')
            ->willReturn(1);

        return $tackleBrand;
    }
}
