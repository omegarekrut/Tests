<?php

namespace Tests\Unit\Module\Seo\View\TextHelper;

use App\Module\Seo\View\TextHelper\DescriptionPreparerHelper;
use JBBCode\Parser;
use Tests\Unit\TestCase;

/**
 * @group seo
 * @group seo-view
 */
class DescriptionPreparerHelperTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testPrepare(string $expectedDescription, string $description)
    {
        $descriptionPreparer = new DescriptionPreparerHelper(new Parser());
        $description = $descriptionPreparer->prepareDescription($description);

        $this->assertEquals($expectedDescription, $description);
    }

    public function getCases(): array
    {
        return [
            'bbcode to html' => [
                'bbcode',
                '[bbcode]bbcode[/bbcode]',
            ],
            'string sanitize' => [
                'bold',
                ' <b>bold</b> ',
            ],
            'sub sentence - 300 characters' => [
                trim(str_repeat('10 char-s ', 30)).'...',
                str_repeat('10 char-s ', 60),
            ],
        ];
    }
}
