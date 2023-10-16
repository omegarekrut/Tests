<?php

namespace Tests\Unit\Twig\AdminHtmlHelper;

use App\Twig\AdminHtmlHelper\ButtonLinkRenderer;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class ButtonLinkRendererTest extends TestCase
{
    private const EXPECTED_LABEL = 'expected label';
    private const EXPECTED_URL = '/expected/link';

    public function testLinkMustBeContainsUrlAndLabel(): void
    {
        $renderer = new ButtonLinkRenderer();
        $link = $renderer(self::EXPECTED_LABEL, self::EXPECTED_URL);

        $this->assertStringContainsString(self::EXPECTED_LABEL, $link);
        $this->assertStringContainsString(self::EXPECTED_URL, $link);
    }
}
