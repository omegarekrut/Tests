<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Rss\Record\CategoryChooser;
use App\Module\YandexZen\ContentPuller\ContentEncodedCollectionFactory;
use App\Domain\Rss\Record\EnclosureChooser;
use Tests\Unit\Domain\Rss\Url\AbsoluteImageUrlGeneratorMock;
use Tests\Unit\TestCase as UnitTestCase;
use ZenRss\ContentPuller\Collection;
use ZenRss\ContentPuller\CollectionBuilder;

class TestCase extends UnitTestCase
{
    /**
     * @param string[] $expectedImages
     * @param string[] $expectedVideo
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function createEnclosureChooserMock(array $expectedImages = [], array $expectedVideo = []): EnclosureChooser
    {
        $self = $this;
        $stub = $this->createMock(EnclosureChooser::class);
        $stub
            ->method('choose')
            ->willReturnCallback(function ($collection, $images, $videos) use ($self, $expectedImages, $expectedVideo): void {
                $self->assertEquals($expectedImages, $images);
                $self->assertEquals($expectedVideo, $videos);
            })
        ;

        return $stub;
    }

    protected function createCollectionBuilderMock(): CollectionBuilder
    {
        $sourceContent = '';

        $stub = $this->createMock(CollectionBuilder::class);
        $stub
            ->method('setSourceContent')
            ->willReturnCallback(function ($source) use (&$sourceContent, $stub) {
                $sourceContent = $source;

                return $stub;
            })
        ;

        $stub
            ->method('createCollection')
            ->willReturnCallback(function () use (&$sourceContent) {
                return new Collection($sourceContent);
            })
        ;

        return $stub;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function createContentEncodedCollectionFactoryMock(): ContentEncodedCollectionFactory
    {
        $stub = $this->createMock(ContentEncodedCollectionFactory::class);
        $stub
            ->method('create')
            ->willReturnCallback(function ($sourceContent, $context) {
                return new Collection($sourceContent);
            })
        ;

        return $stub;
    }

    protected function createUrlGeneratorMock(): AbsoluteImageUrlGeneratorMock
    {
        return new AbsoluteImageUrlGeneratorMock();
    }

    protected function createCategoryChooserMock(): CategoryChooser
    {
        return $this->createMock(CategoryChooser::class);
    }
}
