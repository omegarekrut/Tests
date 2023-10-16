<?php

namespace Tests\Unit\Twig\PublicHtmlHelper;

use App\Twig\AdminHtmlHelper\SubmitButtonRenderer;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class SubmitButtonRendererTest extends TestCase
{
    private const EXPECTED_LABEL = 'expected label';

    public function testLinkMustBeContainsUrlAndLabel(): void
    {
        $renderer = new SubmitButtonRenderer();
        $link = $renderer(self::EXPECTED_LABEL);

        $this->assertStringContainsString(self::EXPECTED_LABEL, $link);
        $this->assertStringContainsString('type="submit"', $link);
    }
}
