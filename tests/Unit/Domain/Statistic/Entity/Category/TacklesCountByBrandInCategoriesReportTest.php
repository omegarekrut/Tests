<?php

namespace Tests\Unit\Domain\Statistic\Entity\Category;

use App\Domain\Category\Collection\CategoryCollection;
use App\Domain\Category\Entity\Category;
use App\Domain\Statistic\Entity\TacklesCountByBrandInCategoriesReport;
use Tests\Unit\TestCase;

/**
 * @group tackleCategories
 */
class TacklesCountByBrandInCategoriesReportTest extends TestCase
{
    public function testRecordsCountInCategoryMustBeCalculatedByCountInNestedCategories(): void
    {
        $rootTackleCategory = $this->createCategory(1, 10);
        $fistNestedCategory = $this->createCategory(2, 20, $rootTackleCategory);
        $deepNestedCategory = $this->createCategory(3, 30, $fistNestedCategory);

        $report = new TacklesCountByBrandInCategoriesReport(
            $rootTackleCategory,
            new CategoryCollection([$rootTackleCategory, $fistNestedCategory, $deepNestedCategory]),
            [
                $this->normalizeRecordCountInCategory($fistNestedCategory),
                $this->normalizeRecordCountInCategory($deepNestedCategory),
            ]
        );

        $expectedCountInRootTackleCategory = $rootTackleCategory->getRecordsCount() + $fistNestedCategory->getRecordsCount() + $deepNestedCategory->getRecordsCount();
        $this->assertEquals($expectedCountInRootTackleCategory, $report->getCountInCategory($rootTackleCategory));

        $expectedCountInFistNestedCategory = $fistNestedCategory->getRecordsCount() + $deepNestedCategory->getRecordsCount();
        $this->assertEquals($expectedCountInFistNestedCategory, $report->getCountInCategory($fistNestedCategory));

        $this->assertEquals($deepNestedCategory->getRecordsCount(), $report->getCountInCategory($deepNestedCategory));
    }

    public function testRecordsCountInParentCategoryMustBeCalculatedByCountInNestedCategoriesEvenIfItIsNotInReportingData(): void
    {
        $rootTackleCategory = $this->createCategory(1, 0);
        $fistNestedCategory = $this->createCategory(2, 0, $rootTackleCategory);
        $deepNestedCategory = $this->createCategory(3, 30, $fistNestedCategory);

        $report = new TacklesCountByBrandInCategoriesReport(
            $rootTackleCategory,
            new CategoryCollection([$rootTackleCategory, $fistNestedCategory, $deepNestedCategory]),
            [
                $this->normalizeRecordCountInCategory($deepNestedCategory),
            ]
        );

        $expectedCountInAllParents = $deepNestedCategory->getRecordsCount();

        $this->assertEquals($expectedCountInAllParents, $report->getCountInCategory($rootTackleCategory));
        $this->assertEquals($expectedCountInAllParents, $report->getCountInCategory($fistNestedCategory));
    }

    public function testRecordsCountMustNotBeGotForNotTackleCategory(): void
    {
        $rootTackleCategory = $this->createCategory(1, 10);
        $nestedTackleCategory = $this->createCategory(2, 20, $rootTackleCategory);
        $notTackleCategory = $this->createCategory(3, 10);

        $report = new TacklesCountByBrandInCategoriesReport(
            $rootTackleCategory,
            new CategoryCollection([$rootTackleCategory, $nestedTackleCategory]),
            [
                $this->normalizeRecordCountInCategory($nestedTackleCategory),
            ]
        );

        $this->assertEquals(0, $report->getCountInCategory($notTackleCategory));
    }

    private function createCategory(int $id, int $recordCount = 0, ?Category $parentCategory = null): Category
    {
        $stub = $this->createMock(Category::class);
        $stub
            ->method('getId')
            ->willReturn($id);
        $stub
            ->method('getRecordsCount')
            ->willReturn($recordCount);
        $stub
            ->method('getParent')
            ->willReturn($parentCategory);

        return $stub;
    }

    /**
     * @return int[]
     */
    private function normalizeRecordCountInCategory(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'parentId' => $category->getParent() ? $category->getParent()->getId() : null,
            'tacklesCount' => $category->getRecordsCount(),
        ];
    }
}
