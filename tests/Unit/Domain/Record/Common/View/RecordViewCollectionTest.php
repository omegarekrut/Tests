<?php

namespace Tests\Unit\Domain\Record\Common\View;

use App\Domain\Record\Common\View\RecordViewCollection;
use App\Domain\Record\Gallery\View\GalleryView;
use App\Domain\Record\Gallery\View\GalleryViewGroup;
use App\Domain\Record\Tidings\View\TidingsView;
use App\Module\Author\View\AuthorView;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class RecordViewCollectionTest extends TestCase
{
    public function testConstructWithWrongTypeElementsShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $wrongElement = (object) [];

        new RecordViewCollection([
            $wrongElement,
        ]);
    }

    public function testGroupGalleryViewsByAuthor(): void
    {
        $collection = new RecordViewCollection([
            $this->createGalleryViewWithAuthorId(1),
            $this->createGalleryViewWithAuthorId(2),
            $this->createGalleryViewWithAuthorId(3),
            $this->createGalleryViewWithAuthorId(2),
        ]);

        $viewsWithGroups = $collection->groupGalleryByAuthor();

        $this->assertCount(3, $viewsWithGroups);
        $this->assertContainsOnlyInstancesOf(GalleryViewGroup::class, $viewsWithGroups);
        $this->assertEquals(1, $viewsWithGroups[0]->count());
        $this->assertEquals(2, $viewsWithGroups[1]->count());
        $this->assertEquals(1, $viewsWithGroups[2]->count());
    }

    public function testGroupGalleryViewsByAuthorShouldGroupOnlyGalleryViews(): void
    {
        $collection = new RecordViewCollection([
            $this->createTidingsViewWithAuthorId(2),
            $this->createTidingsViewWithAuthorId(1),
            $this->createGalleryViewWithAuthorId(1),
            $this->createTidingsViewWithAuthorId(4),
            $this->createGalleryViewWithAuthorId(1),
            $this->createGalleryViewWithAuthorId(3),
        ]);

        $viewsWithGroups = $collection->groupGalleryByAuthor();

        $this->assertCount(5, $viewsWithGroups);

        $this->assertInstanceOf(TidingsView::class, $viewsWithGroups[0]);
        $this->assertInstanceOf(TidingsView::class, $viewsWithGroups[1]);
        $this->assertInstanceOf(GalleryViewGroup::class, $viewsWithGroups[2]);
        $this->assertInstanceOf(TidingsView::class, $viewsWithGroups[3]);
        $this->assertInstanceOf(GalleryViewGroup::class, $viewsWithGroups[4]);
    }

    private function createGalleryViewWithAuthorId(int $authorId): GalleryView
    {
        $galleryViewMock = $this->createMock(GalleryView::class);

        $author = new AuthorView();
        $author->id = $authorId;

        $galleryViewMock->author = $author;

        return $galleryViewMock;
    }

    private function createTidingsViewWithAuthorId(int $authorId): TidingsView
    {
        $galleryViewMock = $this->createMock(TidingsView::class);

        $author = new AuthorView();
        $author->id = $authorId;

        $galleryViewMock->author = $author;

        return $galleryViewMock;
    }
}
