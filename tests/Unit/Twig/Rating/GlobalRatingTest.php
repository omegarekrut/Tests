<?php

namespace Tests\Unit\Twig\Rating;

use App\Twig\Rating\GlobalRating;
use Generator;
use Tests\Unit\TestCase;

class GlobalRatingTest extends TestCase
{
    private GlobalRating $globalRating;

    protected function setUp(): void
    {
        parent::setUp();

        $this->globalRating = new GlobalRating();
    }

    protected function tearDown(): void
    {
        unset(
            $this->globalRating,
        );

        parent::tearDown();
    }

    /**
     * @dataProvider ratingToTextDataProvider
     */
    public function testConvertRatingToText(int $rating, string $expectedText): void
    {
        $ratingAsText = $this->globalRating->asAnnotationText($rating);

        $this->assertEquals($expectedText, $ratingAsText);
    }

    public function ratingToTextDataProvider(): Generator
    {
        yield 0 => [0, 'Читатель FishingSib'];

        yield 100 => [100, 'Читатель FishingSib'];

        yield 101 => [101, 'Энтузиаст FishingSib'];

        yield 1000 => [1000, 'Энтузиаст FishingSib'];

        yield 1001 => [1001, 'Завсегдатай FishingSib'];

        yield 5000 => [5000, 'Завсегдатай FishingSib'];

        yield 5001 => [5001, 'Активист FishingSib'];

        yield 10000 => [10000, 'Активист FishingSib'];

        yield 10001 => [10001, 'Эксперт FishingSib'];

        yield 15000 => [15000, 'Эксперт FishingSib'];

        yield 15001 => [15001, 'Профи FishingSib'];
    }

    public function testGetRatingAsMedal(): void
    {
        $ratingAsMedal = $this->globalRating->oneMedal();

        $this->assertRatingHtmlBlock($ratingAsMedal, 1, false);
    }

    /**
     * @dataProvider ratingToHasRatingMedal
     */
    public function testGetHasRatingMedal(int $rating, bool $expectedValue): void
    {
        $hasRatingMedal = $this->globalRating->hasRatingMedal($rating);

        $this->assertEquals($expectedValue, $hasRatingMedal);
    }

    public function ratingToHasRatingMedal(): Generator
    {
        yield 0 => [0,  false];

        yield 100 => [100, false];

        yield 101 => [101, true];

        yield 1000 => [1000, true];

        yield 1001 => [1001, true];

        yield 5000 => [5000, true];

        yield 5001 => [5001, true];

        yield 10000 => [10000, true];

        yield 10001 => [10001, true];

        yield 15000 => [15000, true];

        yield 15001 => [15001, true];
    }

    /**
     * @dataProvider ratingToMedalDataProvider
     */
    public function testConvertRatingToMedal(int $rating, int $expectedCountMedalElements, bool $expectedDisabledCountMedalElements): void
    {
        $ratingAsMedals = $this->globalRating->asMedals($rating);

        $this->assertRatingHtmlBlock($ratingAsMedals, $expectedCountMedalElements, $expectedDisabledCountMedalElements);
    }

    public function ratingToMedalDataProvider(): Generator
    {
        yield 0 => [0, 1, true];

        yield 100 => [100, 1, true];

        yield 101 => [101, 1, false];

        yield 1000 => [1000, 1, false];

        yield 1001 => [1001, 2, false];

        yield 5000 => [5000, 2, false];

        yield 5001 => [5001, 3, false];

        yield 10000 => [10000, 3, false];

        yield 10001 => [10001, 4, false];

        yield 15000 => [15000, 4, false];

        yield 15001 => [15001, 5, false];
    }

    private function assertRatingHtmlBlock(string $ratingAsMedal, int $expectedCountMedalElements, bool $expectedDisabledCountMedalElements): void
    {
        $countMedalContainers = substr_count($ratingAsMedal, 'class="rating_medals "');
        $countDisabledContainers = substr_count($ratingAsMedal, 'class="rating_medals rating_medals--disabled"');

        $countMedal = substr_count($ratingAsMedal, 'class="rating_medals__item"');

        $this->assertEquals(1, $expectedDisabledCountMedalElements ? $countDisabledContainers : $countMedalContainers);
        $this->assertEquals($expectedCountMedalElements, $countMedal);
    }
}
