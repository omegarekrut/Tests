<?php

namespace Tests\Unit\Domain\Category\Validator\Constraint;

use App\Domain\Category\Collection\CategoryCollection;
use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\Validator\Constraint\CategoryUniqueSlug;
use App\Domain\Category\Validator\Constraint\CategoryUniqueSlugValidator;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class CategoryUniqueSlugValidatorTest extends TestCase
{
    private $executionContext;
    private $categoryRepository;
    private $categoryUniqueSlugValidator;
    private $categoryUniqueSlugConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->categoryRepository = $this->createMock(CategoryRepository::class);

        $this->categoryUniqueSlugValidator = new CategoryUniqueSlugValidator($this->categoryRepository);
        $this->categoryUniqueSlugValidator->initialize($this->executionContext);

        $this->categoryUniqueSlugConstraint = new CategoryUniqueSlug();
    }

    public function testValidateCreateCorrectRootCategory(): void
    {
        $command = new CreateCategoryCommand();
        $command->slug = 'some_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateUpdateCorrectRootCategory(): void
    {
        $category = $this->createCategoryMock('old_slug');

        $command = new UpdateCategoryCommand($category);
        $command->slug = 'new_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateExistedRootCategory(): void
    {
        $category = $this->createCategoryMock('some_slug');

        $this->categoryRepository->method('findRootCategoryBySlug')->willReturn($category);

        $command = new CreateCategoryCommand();
        $command->slug = 'some_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertEquals($this->categoryUniqueSlugConstraint->messageForRootCategory, $this->executionContext->getViolationMessages()[0]);
    }

    public function testValidateCreateCorrectChildCategory(): void
    {
        $parentCategory = $this->createCategoryMock('some_root_slug');

        $command = new CreateCategoryCommand();
        $command->parentCategory = $parentCategory;
        $command->slug = 'some_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateUpdateCorrectChildCategory(): void
    {
        $parentCategory = $this->createCategoryMock('some_root_slug');
        $category = $this->createCategoryMock('old_slug');

        $command = new UpdateCategoryCommand($category);
        $command->parentCategory = $parentCategory;
        $command->slug = 'new_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateWithNullableSlug(): void
    {
        $command = new CreateCategoryCommand();

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateUpdateWithUnmodifiedSlug(): void
    {
        $parentCategory = $this->createCategoryMock('some_root_slug');
        $category = $this->createCategoryMock('old_slug');

        $command = new UpdateCategoryCommand($category);
        $command->parentCategory = $parentCategory;
        $command->slug = 'old_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateChildCategorySlugEqualsRootCategorySlug(): void
    {
        $rootCategory = $this->createCategoryMock('some_root_slug');
        $category = $this->createCategoryMock('some_slug');

        $this->categoryRepository->method('findBySlugInParentCategory')->willReturn($category);

        $command = new CreateCategoryCommand();
        $command->parentCategory = $rootCategory;
        $command->slug = 'some_root_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertEquals($this->categoryUniqueSlugConstraint->messageForParentRootCategory, $this->executionContext->getViolationMessages()[0]);
    }

    public function testValidateCategorySlugEqualsRootChildCategorySlug(): void
    {
        $rootCategory = $this->createCategoryMock('some_root_slug');
        $firstSubCategory = $this->createCategoryMock('some_first_sub_root_slug', $rootCategory);
        $secondSubCategory = $this->createCategoryMock('some_second_sub_root_slug', $rootCategory);

        $rootCategory->getChildren()->add($firstSubCategory);
        $rootCategory->getChildren()->add($secondSubCategory);

        $category = $this->createCategoryMock('some_slug');

        $this->categoryRepository->method('findBySlugInParentCategory')->willReturn($category);

        $command = new CreateCategoryCommand();
        $command->parentCategory = $firstSubCategory;
        $command->slug = 'some_second_sub_root_slug';

        $this->categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertEquals($this->categoryUniqueSlugConstraint->messageForChildCategory, $this->executionContext->getViolationMessages()[0]);
    }

    public function createCategoryMock(string $slug, ?Category $parentCategory = null): Category
    {
        $category = $this->createMock(Category::class);

        $category->method('getSlug')->willReturn($slug);
        $category->method('getParent')->willReturn($parentCategory);
        $category->method('getChildren')->willReturn(new CategoryCollection([]));

        return $category;
    }
}
