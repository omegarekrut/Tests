<?php

namespace Tests\Unit\Domain\Page\Command;

use App\Domain\Page\Command\Handler\UpdatePageHandler;
use App\Domain\Page\Command\UpdatePageCommand;
use App\Domain\Page\Entity\Metadata;
use App\Domain\Page\Entity\Page;
use App\Module\Author\AuthorFactory;

/**
 * @group page
 */
class UpdatePageCommandHandlerTest extends PageCommandTestCase
{
    public function testExecute()
    {
        $sourcePage = $this->createSourcePage();
        $metadata = new Metadata('title', 'description');
        $repository = $this->createPageRepository([
            'slug' => 'slug',
            'title' => 'title',
            'text' => 'text',
            'author' => $sourcePage->getAuthor(),
            'metadata' => $metadata,
        ]);

        $command = new UpdatePageCommand($sourcePage);
        $command->slug = 'slug';
        $command->title = 'title';
        $command->text = 'text';
        $command->metaTitle = $metadata->getTitle();
        $command->metaDescription = $metadata->getDescription();

        $handler = new UpdatePageHandler($repository);
        $handler->handle($command);
    }

    private function createSourcePage(): Page
    {
        return new Page(
            'source slug',
            'source title',
            'source text',
            AuthorFactory::createAnonymousFromUsername('author'),
            new Metadata('source meta title', 'source meta description')
        );
    }
}
