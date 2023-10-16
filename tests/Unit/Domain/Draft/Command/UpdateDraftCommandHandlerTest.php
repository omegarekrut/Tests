<?php

namespace Tests\Unit\Domain\Draft\Command;

use App\Domain\Draft\Command\Handler\UpdateDraftHandler;
use App\Domain\Draft\Command\UpdateDraftCommand;
use App\Domain\Draft\Entity\Draft;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group draft
 */
class UpdateDraftCommandHandlerTest extends TestCase
{
    public function testDraftIsChanged()
    {
        $draft = new Draft(
            'old title',
            'old text'
        );

        $command = new UpdateDraftCommand($draft);
        $command->title = 'new title';
        $command->text = 'new text';

        $objectManager = new ObjectManagerMock();

        $handler = new UpdateDraftHandler($objectManager);
        $handler->handle($command);

        $this->assertEquals($command->title, $draft->getTitle());
        $this->assertEquals($command->text, $draft->getText());
    }
}
