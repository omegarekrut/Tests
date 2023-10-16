<?php

namespace Tests\Unit\Domain\WeeklyLetter\Service;

use App\Domain\WeeklyLetter\Service\WeeklyLetterSubjectGenerator;
use Tests\Unit\TestCase;

/**
 * @group weekly-letter
 */
class WeeklyLetterSubjectGeneratorTest extends TestCase
{
    /**
     * @dataProvider getWeeklyLetterNumberAndExpectedSubject
     */
    public function testGenerate(int $weeklyLetterNumber, string $expectedSubject): void
    {
        $weeklyLetterSubject = WeeklyLetterSubjectGenerator::generate($weeklyLetterNumber);

        $this->assertEquals($expectedSubject, $weeklyLetterSubject);
    }

    /**
     * @return mixed[]
     */
    public function getWeeklyLetterNumberAndExpectedSubject(): array
    {
        return [
            [2, 'Рассылка №2. Самое интересное за неделю'],
            [7, 'Рассылка №7. Самое интересное за неделю'],
            [41, 'Рассылка №41. Самое интересное за неделю'],
        ];
    }
}
