<?php

namespace Tests\Unit\Domain\CompanyLetter\View;

use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Domain\CompanyLetter\View\InnovationBlockView;
use App\Domain\CompanyLetter\View\InnovationBlockViewFactory;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformer;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\BBCode\BBCodeToHtmlFilter;
use Tests\Functional\TestCase;

class InnovationBlockViewFactoryTest extends TestCase
{
    public function testCreateInnovationBlockView(): void
    {
        $innovationBlockViewFactory = new InnovationBlockViewFactory(
            $this->createMockBBCodeToHtmlFilter(),
            $this->getImageTransformerFactory()
        );

        $firstInnovationBlock = $this->getInnovationBlock();

        $innovationBlockViews = $innovationBlockViewFactory->createByCollection([
            $firstInnovationBlock,
        ]);

        $this->assertCount(1, $innovationBlockViews);
        $this->assertContainsOnlyInstancesOf(InnovationBlockView::class, $innovationBlockViews);

        $expectedData = sprintf('<bb-filter>%s</bb-filter>', $firstInnovationBlock->getData());

        $this->assertEquals($firstInnovationBlock->getTitle(), $innovationBlockViews[0]->title);
        $this->assertEquals($this->getExpectedImageTransformer(), $innovationBlockViews[0]->image);
        $this->assertEquals($expectedData, $innovationBlockViews[0]->data);
    }

    private function createMockBBCodeToHtmlFilter(): BBCodeToHtmlFilter
    {
        $filter = $this->createMock(BBCodeToHtmlFilter::class);
        $filter
            ->method('__invoke')
            ->willReturnCallback(static fn (string $input): string => sprintf('<bb-filter>%s</bb-filter>', $input));

        return $filter;
    }

    private function getImageTransformerFactory(): ImageTransformerFactory
    {
        $imageTransformerFactory = $this->createMock(ImageTransformerFactory::class);

        $imageTransformerFactory
            ->method('create')
            ->willReturn($this->getExpectedImageTransformer());

        return $imageTransformerFactory;
    }

    private function getExpectedImageTransformer(): ImageTransformer
    {
        return $this->createMock(ImageTransformer::class);
    }

    private function getInnovationBlock(): InnovationBlock
    {
        $innovationBlock = $this->createMock(InnovationBlock::class);

        $innovationBlock
            ->method('getTitle')
            ->willReturn($this->getFaker()->title);

        $innovationBlock
            ->method('getImage')
            ->willReturn(new Image('innovation.jpeg'));

        $innovationBlock
            ->method('getData')
            ->willReturn($this->getFaker()->text);

        return $innovationBlock;
    }
}
