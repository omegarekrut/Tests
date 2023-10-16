<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\ConvertUrlToLinkFilter;
use Tests\Unit\TestCase;

class ConvertUrlToLinkFilterTest extends TestCase
{
    /** @var ConvertUrlToLinkFilter */
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new ConvertUrlToLinkFilter();
    }

    public function testDoNotFilterTextWithoutLinks(): void
    {
        $expectedText = 'foo bar';

        $this->assertEquals($expectedText, ($this->filter)($expectedText));
    }

    public function testFilterTextContainsLink(): void
    {
        $sourceText = 'start http://foo.bar end';
        $expectedText = 'start <a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a> end';

        $this->assertEquals($expectedText, ($this->filter)($sourceText));
    }
}
