<?php

namespace Tests\Unit\Domain\FleaMarket\Validator\Constraint;

use App\Domain\FleaMarket\Collection\CategoryCollection;
use App\Domain\FleaMarket\Command\CreateCategoryCommand;
use App\Domain\FleaMarket\Command\UpdateCategoryCommand;
use App\Domain\FleaMarket\Entity\Category;
use App\Domain\FleaMarket\Repository\FleaMarketCategoryRepository;
use App\Domain\FleaMarket\Validator\Constraint\CategoryUniqueSlug;
use App\Domain\FleaMarket\Validator\Constraint\CategoryUniqueSlugValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class CategoryUniqueSlugValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private CategoryUniqueSlug $categoryUniqueSlugConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->categoryUniqueSlugConstraint = new CategoryUniqueSlug();
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->categoryUniqueSlugConstraint
        );

        parent::tearDown();
    }

    public function testValidateCreateCorrectRootCategory(): void
    {
        $command = new CreateCategoryCommand();
        $command->slug = 'some_slug';

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator();

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateUpdateCorrectRootCategory(): void
    {
        $category = $this->createCategoryMock('old_slug');

        $command = new UpdateCategoryCommand($category);
        $command->slug = 'new_slug';

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator();

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateExistedRootCategory(): void
    {
        $category = $this->createCategoryMock('some_slug');

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator('findBySlug', $category);

        $command = new CreateCategoryCommand();
        $command->slug = 'some_slug';

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertEquals($this->categoryUniqueSlugConstraint->message, $this->executionContext->getViolationMessages()[0]);
    }

    public function testValidateCreateCorrectChildCategory(): void
    {
        $parentCategory = $this->createCategoryMock('some_root_slug');

        $command = new CreateCategoryCommand();
        $command->parentCategory = $parentCategory;
        $command->slug = 'some_slug';

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator();

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateUpdateCorrectChildCategory(): void
    {
        $parentCategory = $this->createCategoryMock('some_root_slug');
        $category = $this->createCategoryMock('old_slug');

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator();

        $command = new UpdateCategoryCommand($category);
        $command->parentCategory = $parentCategory;
        $command->slug = 'new_slug';

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateWithNullableSlug(): void
    {
        $command = new CreateCategoryCommand();

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator();

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateUpdateWithUnmodifiedSlug(): void
    {
        $parentCategory = $this->createCategoryMock('some_root_slug');
        $category = $this->createCategoryMock('old_slug');

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator();

        $command = new UpdateCategoryCommand($category);
        $command->parentCategory = $parentCategory;
        $command->slug = 'old_slug';

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidateChildCategorySlugEqualsRootCategorySlug(): void
    {
        $rootCategory = $this->createCategoryMock('some_root_slug');
        $category = $this->createCategoryMock('some_slug');

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator('findBySlug', $category);

        $command = new CreateCategoryCommand();
        $command->parentCategory = $rootCategory;
        $command->slug = 'some_root_slug';

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertEquals($this->categoryUniqueSlugConstraint->message, $this->executionContext->getViolationMessages()[0]);
    }

    public function testValidateCategorySlugEqualsRootChildCategorySlug(): void
    {
        $rootCategory = $this->createCategoryMock('some_root_slug');
        $firstSubCategory = $this->createCategoryMock('some_first_sub_root_slug', $rootCategory);
        $secondSubCategory = $this->createCategoryMock('some_second_sub_root_slug', $rootCategory);

        $rootCategory->getChildren()->add($firstSubCategory);
        $rootCategory->getChildren()->add($secondSubCategory);

        $category = $this->createCategoryMock('some_slug');

        $categoryUniqueSlugValidator = $this->createCategoryUniqueSlugValidator('findBySlug', $category);

        $command = new CreateCategoryCommand();
        $command->parentCategory = $firstSubCategory;
        $command->slug = 'some_second_sub_root_slug';

        $categoryUniqueSlugValidator->validate($command, $this->categoryUniqueSlugConstraint);

        $this->assertEquals($this->categoryUniqueSlugConstraint->message, $this->executionContext->getViolationMessages()[0]);
    }

    public function createCategoryMock(string $slug, ?Category $parentCategory = null): Category
    {
        $category = $this->createMock(Category::class);

        $category->method('getSlug')->willReturn($slug);
        $category->method('getParent')->willReturn($parentCategory);
        $category->method('getChildren')->willReturn(new CategoryCollection([]));

        return $category;
    }

    public function createCategoryUniqueSlugValidator(string $method = 'findBySlug', ?Category $response = null): CategoryUniqueSlugValidator
    {
        $categoryRepository = $this->createMock(FleaMarketCategoryRepository::class);
        $response = new ArrayCollection((array) $response);

        $categoryRepository->method($method)->willReturn($response);

        $categoryUniqueSlugValidator = new CategoryUniqueSlugValidator($categoryRepository);
        $categoryUniqueSlugValidator->initialize($this->executionContext);

        return $categoryUniqueSlugValidator;
    }
}
