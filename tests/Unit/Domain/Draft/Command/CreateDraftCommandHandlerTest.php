<?php

namespace Tests\Unit\Domain\Draft\Command;

use App\Domain\Draft\Command\CreateDraftCommand;
use App\Domain\Draft\Command\Handler\CreateDraftHandler;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group draft
 */
class CreateDraftCommandHandlerTest extends TestCase
{
    public function testDraftIsCreatedAndSaved()
    {
        $command = new CreateDraftCommand();
        $command->title = 'draft title';
        $command->text = 'draft text';

        $objectManager = new ObjectManagerMock();

        $commandHandler = new CreateDraftHandler($objectManager);
        $commandHandler->handle($command);

        $savedDraft = $objectManager->getLastPersistedObject();

        $this->assertEquals($command->title, $savedDraft->getTitle());
        $this->assertEquals($command->text, $savedDraft->getText());
    }
}
