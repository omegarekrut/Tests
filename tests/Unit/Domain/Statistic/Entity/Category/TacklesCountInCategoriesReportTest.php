<?php

namespace Tests\Unit\Domain\Statistic\Entity\Category;

use App\Domain\Category\Collection\CategoryCollection;
use App\Domain\Category\Entity\Category;
use App\Domain\Statistic\Entity\TacklesCountInCategoriesReport;
use Tests\Unit\TestCase;

/**
 * @group tackleCategories
 */
class TacklesCountInCategoriesReportTest extends TestCase
{
    public function testRecordCountMustBeGotFromReportByCategoryRecordsCountWithChild(): void
    {
        $tackleCategory = $this->createTackleCategory(10);

        $report = new TacklesCountInCategoriesReport(new CategoryCollection([$tackleCategory]));

        $this->assertEquals($tackleCategory->getRecordsCountWithChild(), $report->getCountInCategory($tackleCategory));
    }

    public function testRecordsCountMustNotBeGotForNotTackleCategory(): void
    {
        $tackleCategory = $this->createTackleCategory(10);
        $notTackleCategory = $this->createTackleCategory(20);

        $report = new TacklesCountInCategoriesReport(new CategoryCollection([$tackleCategory]));

        $this->assertEquals(0, $report->getCountInCategory($notTackleCategory));
    }

    private function createTackleCategory(int $recordsCountWithChildren): Category
    {
        $stub = $this->createMock(Category::class);
        $stub
            ->method('getRecordsCountWithChild')
            ->willReturn($recordsCountWithChildren);

        return $stub;
    }
}
