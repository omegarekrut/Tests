<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\AddBlankToLinkFilter;
use Tests\Unit\TestCase;

class AddBlankToLinkFilterTest extends TestCase
{
    /**
     * @var AddBlankToLinkFilter
     */
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new AddBlankToLinkFilter();
    }

    public function testDoNotFilterTextWithoutLinks(): void
    {
        $expectedText = 'foo bar';

        $this->assertEquals($expectedText, ($this->filter)($expectedText));
    }

    public function testFilterTextContainsLink(): void
    {
        $sourceText = 'start <a href="http://foo.bar">http://foo.bar</a> end';
        $expectedText = 'start <a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a> end';

        $this->assertEquals($expectedText, ($this->filter)($sourceText));
    }

    public function testFilterTextContainsLinkWithTargetBlank(): void
    {
        $sourceText = 'start <a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a> end';
        $expectedText = 'start <a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a> end';

        $this->assertEquals($expectedText, ($this->filter)($sourceText));
    }
}
