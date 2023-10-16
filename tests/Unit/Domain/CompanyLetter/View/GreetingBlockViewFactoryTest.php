<?php

namespace Tests\Unit\Domain\CompanyLetter\View;

use App\Domain\CompanyLetter\Entity\GreetingBlock;
use App\Domain\CompanyLetter\View\GreetingBlockViewFactory;
use App\Util\StringFilter\BBCode\BBCodeToHtmlFilter;
use Tests\Unit\TestCase;

class GreetingBlockViewFactoryTest extends TestCase
{
    public function testCreateGreetingBlockView(): void
    {
        $greetingBlockViewFactory = new GreetingBlockViewFactory(
            $this->createMockBBCodeToHtmlFilter(),
        );

        $greetingBlock = $this->createMock(GreetingBlock::class);
        $greetingBlock
            ->method('getData')
            ->willReturn('test data');

        $greetingBlockView = $greetingBlockViewFactory->create($greetingBlock);

        $expectedData = sprintf('<bb-filter>%s</bb-filter>', $greetingBlock->getData());
        $this->assertEquals($expectedData, $greetingBlockView->data);
    }

    private function createMockBBCodeToHtmlFilter(): BBCodeToHtmlFilter
    {
        $filter = $this->createMock(BBCodeToHtmlFilter::class);
        $filter
            ->method('__invoke')
            ->willReturnCallback(static fn (string $input): string => sprintf('<bb-filter>%s</bb-filter>', $input));

        return $filter;
    }
}
