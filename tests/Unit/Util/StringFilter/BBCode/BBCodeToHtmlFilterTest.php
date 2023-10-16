<?php

namespace Tests\Unit\Util\StringFilter\BBCode;

use App\Util\StringFilter\BBCode\BBCodeToHtmlFilter;
use JBBCode\Parser;
use Tests\Unit\TestCase;

class BBCodeToHtmlFilterTest extends TestCase
{
    public function testDecoration(): void
    {
        $bbCodeToHtmlFilter = new BBCodeToHtmlFilter(
            $this->createBBCodeParser('source', 'parse <b>result</b> [invlid-tag]tag value[/invalid-tag]')
        );

        $actualText = $bbCodeToHtmlFilter('source');

        $this->assertEquals('parse <b>result</b> tag value', $actualText);
    }

    private function createBBCodeParser(string $source, string $result): Parser
    {
        $stub = $this->createMock(Parser::class);
        $stub
            ->expects($this->once())
            ->method('parse')
            ->with($source);
        $stub
            ->expects($this->once())
            ->method('getAsHtml')
            ->willReturn($result);

        return $stub;
    }
}
