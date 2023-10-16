<?php

namespace Tests\Unit\Twig\AdminHtmlHelper;

use App\Twig\AdminHtmlHelper\PageHeaderRenderer;
use Tests\Unit\TestCase;

class PageHeaderRendererTest extends TestCase
{
    private const EXPECTED_HEADING = 'expected heading';

    public function testHeaderMustBeContainsHeading(): void
    {
        $pageHeaderRenderer = new PageHeaderRenderer();
        $header = $pageHeaderRenderer(self::EXPECTED_HEADING);

        $this->assertStringContainsString('<h1>'.self::EXPECTED_HEADING.'</h1>', $header);
    }
}
