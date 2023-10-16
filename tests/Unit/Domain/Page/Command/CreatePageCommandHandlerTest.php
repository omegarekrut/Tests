<?php

namespace Tests\Unit\Domain\Page\Command;

use App\Auth\Visitor\Visitor;
use App\Domain\Page\Command\CreatePageCommand;
use App\Domain\Page\Command\Handler\CreatePageHandler;
use App\Domain\Page\Entity\Metadata;
use App\Module\Author\AuthorInterface;

/**
 * @group page
 */
class CreatePageCommandHandlerTest extends PageCommandTestCase
{
    public function testHandle()
    {
        $expectedAuthor = $this->createMock(AuthorInterface::class);
        $expectedMetadata = new Metadata('title', 'description');

        $commandHandler = new CreatePageHandler(
            $this->createPageRepository([
                'slug' => 'slug',
                'title' => 'title',
                'text' => 'text',
                'author' => $expectedAuthor,
                'metadata' => $expectedMetadata,
            ]),
            $this->createVisitor($expectedAuthor)
        );

        $command = new CreatePageCommand();
        $command->slug = 'slug';
        $command->title = 'title';
        $command->text = 'text';
        $command->metaTitle = $expectedMetadata->getTitle();
        $command->metaDescription = $expectedMetadata->getDescription();

        $commandHandler->handle($command);
    }

    private function createVisitor(AuthorInterface $expectedAuthor): Visitor
    {
        $stub = $this->createMock(Visitor::class);
        $stub
            ->expects($this->once())
            ->method('getAuthor')
            ->willReturn($expectedAuthor)
        ;

        return $stub;
    }
}
