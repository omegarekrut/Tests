<?php

namespace Tests\Unit\Domain\Category\Validator\Constraint;

use App\Domain\Category\Collection\CategoryCollection;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\Validator\Constraint\ParentCategoryIsNotFromCategoryBranch;
use App\Domain\Category\Validator\Constraint\ParentCategoryIsNotFromCategoryBranchValidator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group category
 */
class ParentCategoryIsNotFromCategoryBranchValidatorTest extends TestCase
{
    /** @var Category */
    private $parentCategory;

    /** @var Category */
    private $category;

    /** @var ValidatorExecutionContextMock */
    private $executionContext;

    /** @var ParentCategoryIsNotFromCategoryBranchValidator */
    private $parentCategoryIsNotFromCategoryBranchValidator;

    /** @var ParentCategoryIsNotFromCategoryBranch */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentCategory = new Category(
            'Parent Category Title',
            'Parent Category Description',
            'parent-category-slug'
        );
        $this->category = new Category(
            'Category Title',
            'Category Description',
            'category-slug',
            $this->parentCategory
        );

        $this->executionContext = new ValidatorExecutionContextMock();

        $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $categoryRepositoryMock->method('findAllFromCategoryBranchOrderedByHierarchy')
            ->with($this->category)
            ->willReturn(new CategoryCollection([
                $this->category,
            ]));

        $this->parentCategoryIsNotFromCategoryBranchValidator = new ParentCategoryIsNotFromCategoryBranchValidator(
            $categoryRepositoryMock,
            new PropertyAccessor()
        );
        $this->parentCategoryIsNotFromCategoryBranchValidator->initialize($this->executionContext);

        $this->constraint = new ParentCategoryIsNotFromCategoryBranch();
    }

    protected function tearDown(): void
    {
        unset(
            $this->constraint,
            $this->parentCategoryIsNotFromCategoryBranchValidator,
            $this->executionContext,
            $this->category,
            $this->parentCategory
        );

        parent::tearDown();
    }

    public function testValidatorNotSupportAnotherConstraintType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Constraint must be instance %s',
            ParentCategoryIsNotFromCategoryBranch::class
        ));

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate(
            null,
            $this->createMock(Constraint::class)
        );
    }

    public function testValidationFailIfParentCategoryIsFromCategoryBranch(): void
    {
        $categoryAndParentCategory = (object) [
            'parentCategory' => $this->category,
            'category' => $this->category,
        ];

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate($categoryAndParentCategory, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationSkippedIfParentCategoryIsNull(): void
    {
        $categoryAndParentCategory = (object) [
            'parentCategory' => null,
            'category' => $this->category,
        ];

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate($categoryAndParentCategory, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationSkippedIfCategoryIsNull(): void
    {
        $categoryAndParentCategory = (object) [
            'parentCategory' => $this->parentCategory,
            'category' => null,
        ];

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate($categoryAndParentCategory, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationSkippedIfCategoryHasNotCorrectType(): void
    {
        $categoryAndParentCategory = (object) [
            'parentCategory' => $this->parentCategory,
            'category' => 'not category object',
        ];

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate($categoryAndParentCategory, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationSkippedIfParentCategoryHasNotCorrectType(): void
    {
        $categoryAndParentCategory = (object) [
            'parentCategory' => 'not category object',
            'category' => $this->category,
        ];

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate($categoryAndParentCategory, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationPassWhenParentCategoryNotFromCategoryBranch(): void
    {
        $categoryAndParentCategory = (object) [
            'parentCategory' => $this->parentCategory,
            'category' => $this->category,
        ];

        $this->parentCategoryIsNotFromCategoryBranchValidator->validate($categoryAndParentCategory, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }
}
