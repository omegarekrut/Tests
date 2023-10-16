<?php

namespace Tests\Unit\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Command\TackleBrand\CreateTackleBrandCommand;
use App\Domain\Record\Tackle\Command\TackleBrand\Handler\CreateTackleBrandHandler;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Repository\TackleBrandRepository;
use Tests\Unit\TestCase;

/**
 * @group tackle
 * @group tackle-brand
 */
class CreateTackleBrandCommandHandlerTest extends TestCase
{
    public function testHandle()
    {
        $commandHandler = new CreateTackleBrandHandler($this->getTackleBrandRepository());

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
                $this->assertEquals('test', $entity->getTitle());
            });

        return $repository;
    }

    private function getCommand(): CreateTackleBrandCommand
    {
        $command = new CreateTackleBrandCommand();
        $command->title = 'test';

        return $command;
    }
}
