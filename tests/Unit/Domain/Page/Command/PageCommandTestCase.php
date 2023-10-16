<?php

namespace Tests\Unit\Domain\Page\Command;

use App\Domain\Page\Entity\Page;
use App\Domain\Page\Repository\PageRepository;
use Tests\Unit\TestCase;

abstract class PageCommandTestCase extends TestCase
{
    protected function createPageRepository(?array $expectedData = null): PageRepository
    {
        $stub = $this->createMock(PageRepository::class);

        if ($expectedData) {
            $stub
                ->expects($this->once())
                ->method('save')
                ->willReturnCallback(function (Page $page) use ($expectedData) {
                    $this->assertEquals($expectedData['slug'], $page->getSlug());
                    $this->assertEquals($expectedData['title'], $page->getTitle());
                    $this->assertEquals($expectedData['text'], $page->getText());
                    $this->assertEquals($expectedData['author'], $page->getAuthor());
                    $this->assertEquals($expectedData['metadata'], $page->getMetadata());
                });
        } else {
            $stub
                ->expects($this->never())
                ->method('save');
        }

        return $stub;
    }
}
