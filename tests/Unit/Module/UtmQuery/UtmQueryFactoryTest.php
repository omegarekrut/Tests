<?php

namespace Tests\Unit\Module\UtmQuery;

use App\Module\UtmQuery\UtmQueryFactory;
use Tests\Unit\TestCase;

/**
 * @group weekly-letter
 */
class UtmQueryFactoryTest extends TestCase
{
    /**
     * @dataProvider utmContentAndTermProvider
     */
    public function testCreateUtmQueryForWeeklyLetter(string $utmContent, string $utmTerm, string $expectedQuery): void
    {
        $utmQueryFactory = new UtmQueryFactory();

        $utmQuery = $utmQueryFactory->createForWeeklyLetter($utmContent, $utmTerm);

        $this->assertEquals($expectedQuery, $utmQuery);
    }

    /**
     * @return mixed[]
     */
    public function utmContentAndTermProvider(): array
    {
        return [
            [
                '',
                '',
                'utm_source=email&utm_medium=free&utm_campaign=weekly_email',
            ],
            [
                '',
                'tidings',
                'utm_source=email&utm_medium=free&utm_campaign=weekly_email&utm_term=tidings',
            ],
            [
                'content',
                '',
                'utm_source=email&utm_medium=free&utm_campaign=weekly_email&utm_content=content',
            ],
            [
                'content',
                'tidings',
                'utm_source=email&utm_medium=free&utm_campaign=weekly_email&utm_content=content&utm_term=tidings',
            ],
        ];
    }
}
