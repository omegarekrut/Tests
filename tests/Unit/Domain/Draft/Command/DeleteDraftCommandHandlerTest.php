<?php

namespace Tests\Unit\Domain\Draft\Command;

use App\Domain\Draft\Command\DeleteDraftCommand;
use App\Domain\Draft\Command\Handler\DeleteDraftHandler;
use App\Domain\Draft\Entity\Draft;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group draft
 */
class DeleteDraftCommandHandlerTest extends TestCase
{
    public function testDraftIsDeleted()
    {
        $draft = $this->createMock(Draft::class);
        $command = new DeleteDraftCommand($draft);

        $objectManager = new ObjectManagerMock();

        $handler = new DeleteDraftHandler($objectManager);
        $handler->handle($command);

        $this->assertTrue($objectManager->isRemoved($draft));
    }
}
