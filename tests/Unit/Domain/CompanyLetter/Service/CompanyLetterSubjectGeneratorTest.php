<?php

namespace Tests\Unit\Domain\CompanyLetter\Service;

use App\Domain\CompanyLetter\Service\CompanyLetterSubjectGenerator;
use Carbon\Carbon;
use Tests\Unit\TestCase;

class CompanyLetterSubjectGeneratorTest extends TestCase
{
    /**
     * @dataProvider getMonths
     */
    public function testGenerate(int $numMonth, string $nameMonth): void
    {
        $expectedSubject = sprintf('Дайджест событий вашего бизнес-аккаунта на сайте fishingsib.ru за %s 2022', $nameMonth);
        $period = Carbon::createFromDate(2022, $numMonth, 1);

        $companyLetterSubject = CompanyLetterSubjectGenerator::generate($period);

        $this->assertEquals($expectedSubject, $companyLetterSubject);
    }

    public function getMonths(): array
    {
        return [
            'jan' => [1, 'январь'],
            'feb' => [2, 'февраль'],
            'mar' => [3, 'март'],
            'apr' => [4, 'апрель'],
            'may' => [5, 'май'],
            'jun' => [6, 'июнь'],
            'jul' => [7, 'июль'],
            'aug' => [8, 'август'],
            'sep' => [9, 'сентябрь'],
            'oct' => [10, 'октябрь'],
            'nov' => [11, 'ноябрь'],
            'dec' => [12, 'декабрь'],
        ];
    }
}
