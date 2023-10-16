<?php

namespace Tests\Unit\Domain\Page\Command;

use App\Domain\Page\Command\DeletePageCommand;
use App\Domain\Page\Command\Handler\DeletePageHandler;
use App\Domain\Page\Entity\Page;
use App\Domain\Page\Repository\PageRepository;
use Tests\Unit\TestCase;

/**
 * @group page
 */
class DeletePageCommandHandlerTest extends TestCase
{
    public function testExecute()
    {
        $expectedPage = $this->createMock(Page::class);
        $command = new DeletePageCommand($expectedPage);
        $handler = new DeletePageHandler($this->createPageRepository($expectedPage));

        $handler->handle($command);
    }

    private function createPageRepository(Page $page): PageRepository
    {
        $stub = $this->createMock(PageRepository::class);
        $stub
            ->expects($this->once())
            ->method('delete')
            ->with($page)
        ;

        return $stub;
    }
}
