<?php

namespace Tests\Unit\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleBrand\DeleteTackleBrandCommand;
use App\Domain\Record\Tackle\Command\TackleBrand\Handler\DeleteTackleBrandHandler;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Repository\TackleBrandRepository;
use Tests\Unit\TestCase;

/**
 * @group tackle
 * @group tackle-brand
 */
class DeleteTackleBrandCommandHandlerTest extends TestCase
{
    public function testHandle()
    {
        $commandHandler = new DeleteTackleBrandHandler($this->getTackleBrandRepository());

        $commandHandler->handle($this->getCommand());
    }

    private function getTackleBrandRepository(): TackleBrandRepository
    {
        $repository = $this->createMock(TackleBrandRepository::class);

        $repository
            ->expects($this->once())
            ->method('delete')
            ->willReturnCallback(function ($entity) {
                $this->assertInstanceOf(TackleBrand::class, $entity);
                $this->assertEquals(1, $entity->getId());
            });

        return $repository;
    }

    private function getCommand(): DeleteTackleBrandCommand
    {
        $command = new DeleteTackleBrandCommand($this->getTackleBrandMock());

        return $command;
    }

    private function getTackleBrandMock(): TackleBrand
    {
        $tackleBrand = $this->createMock(TackleBrand::class);
        $tackleBrand->method('getId')
            ->willReturn(1);

        return $tackleBrand;
    }
}
