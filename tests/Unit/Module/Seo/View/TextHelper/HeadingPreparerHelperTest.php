<?php

namespace Tests\Unit\Module\Seo\View\TextHelper;

use App\Module\Seo\View\TextHelper\HeadingPreparerHelper;
use Tests\Unit\TestCase;

/**
 * @group seo
 * @group seo-view
 */
class HeadingPreparerHelperTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testPrepareTitle(string $expectedTitle, string $title, int $pageNumber)
    {
        $headingPreparer = new HeadingPreparerHelper();

        $this->assertEquals(
            $expectedTitle,
            $headingPreparer->prepareHeading($title, $pageNumber)
        );
    }

    public function getCases(): array
    {
        return [
            'blank source data' => [
                '',
                '',
                1,
            ],
            'only heading' => [
                'Title',
                'Title',
                1,
            ],
            'heading and page number' => [
                'Title. Страница 2',
                'Title.',
                2,
            ],
        ];
    }
}
