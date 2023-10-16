<?php

namespace Tests\Unit\Util\StringFilter\BBCode;

use App\Util\StringFilter\BBCode\BBCodeToTextFilter;
use JBBCode\Parser;
use Tests\Unit\TestCase;

class BBCodeToTextFilterTest extends TestCase
{
    public function testDecoration(): void
    {
        $bbCodeToTextFilter = new BBCodeToTextFilter(
            $this->createBBCodeParser('source', 'parse [b]result[/b] [invlid-tag]tag value[/invalid-tag]')
        );

        $actualText = $bbCodeToTextFilter('source');

        $this->assertEquals('parse result tag value', $actualText);
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
            ->method('getAsText')
            ->willReturn($result);

        return $stub;
    }
}
