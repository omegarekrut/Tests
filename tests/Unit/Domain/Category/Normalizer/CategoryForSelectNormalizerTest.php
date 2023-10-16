<?php

namespace Tests\Unit\Domain\Category\Normalizer;

use App\Domain\Category\Collection\CategoryCollection;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Normalizer\CategoryForSelectNormalizer;
use App\Domain\Category\Repository\CategoryRepository;
use Tests\Unit\TestCase;

/**
 * @group category
 */
class CategoryForSelectNormalizerTest extends TestCase
{
    public function testNormalizeFirstChildrenOfTheCategoryBySlugWithInvalidArgumentException(): void
    {
        $categorySlug = 'categorySlug';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Category slug "%s" is not correct', $categorySlug));

        $normalizer = new CategoryForSelectNormalizer($this->createCategoryRepositoryForFindBySlug());

        $normalizer->normalizeFirstChildrenOfTheCategoryBySlug($categorySlug);
    }

    public function testNormalizeFirstChildrenOfTheCategoryBySlug(): void
    {
        $childrenCategories = $this->createTitledCategoriesWithIds(3);
        $rootCategory = $this->createCategoryWithChildren($childrenCategories);

        $normalizer = new CategoryForSelectNormalizer($this->createCategoryRepositoryForFindBySlug($rootCategory));

        $normalizedCategories = $normalizer->normalizeFirstChildrenOfTheCategoryBySlug('category-slug');

        $this->assertCount(count($childrenCategories), $normalizedCategories);

        foreach ($childrenCategories as $childCategory) {
            assert($childCategory instanceof Category);

            $this->assertArrayHasKey($childCategory->getId(), $normalizedCategories);
            $this->assertEquals($childCategory->getTitle(), $normalizedCategories[$childCategory->getId()]);
        }
    }

    private function createCategoryRepositoryForFindBySlug(?Category $category = null): CategoryRepository
    {
        $stub = $this->createMock(CategoryRepository::class);
        $stub
            ->method('findRootCategoryBySlug')
            ->willReturn($category);

        return $stub;
    }

    private function createCategoryWithChildren(CategoryCollection $childrenCategories): Category
    {
        $stub = $this->createMock(Category::class);
        $stub
            ->method('getChildren')
            ->willReturn($childrenCategories);

        return $stub;
    }

    private function createTitledCategoriesWithIds(int $categoriesCount): CategoryCollection
    {
        $categories = [];

        for ($id = 1; $id <= $categoriesCount; $id++) {
            $category = $this->createMock(Category::class);
            $category
                ->method('getId')
                ->willReturn($id);
            $category
                ->method('getTitle')
                ->willReturn(sprintf('title of #%s category', $id));

            $categories[] = $category;
        }

        return new CategoryCollection($categories);
    }
}
