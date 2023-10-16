<?php

namespace Tests\Unit\Module\Seo\View\TextHelper;

use App\Module\Seo\View\TextHelper\TitlePreparerHelper;
use Tests\Unit\TestCase;

/**
 * @group seo
 * @group seo-view
 */
class TitlePreparerHelperTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testPrepareTitle(string $expectedTitle, string $title, int $pageNumber): void
    {
        $titlePreparer = new TitlePreparerHelper();

        $this->assertEquals(
            $expectedTitle,
            $titlePreparer->prepareTitle($title, $pageNumber)
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
            'only title' => [
                'Title',
                '<b>Title</b>',
                1,
            ],
            'title and page number' => [
                'Title! Страница 2',
                'Title!',
                2,
            ],
            'blank title and filled page number' => [
                'Страница 2',
                '',
                2,
            ],
        ];
    }
}
