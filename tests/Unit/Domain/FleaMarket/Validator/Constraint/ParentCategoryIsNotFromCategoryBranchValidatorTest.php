<?php

namespace Tests\Unit\Domain\FleaMarket\Validator\Constraint;

use App\Domain\FleaMarket\Entity\Category;
use App\Domain\FleaMarket\Validator\Constraint\ParentCategoryIsNotFromCategoryBranch;
use App\Domain\FleaMarket\Validator\Constraint\ParentCategoryIsNotFromCategoryBranchValidator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group flea-market
 */
class ParentCategoryIsNotFromCategoryBranchValidatorTest extends TestCase
{
    private Category $parentCategory;
    private Category $category;
    private ValidatorExecutionContextMock $executionContext;
    private ParentCategoryIsNotFromCategoryBranchValidator $parentCategoryIsNotFromCategoryBranchValidator;
    private ParentCategoryIsNotFromCategoryBranch $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentCategory = new Category(
            Uuid::uuid4(),
            'Parent Category Title',
            'parent-category-slug'
        );
        $this->category = new Category(
            Uuid::uuid4(),
            'Category Title',
            'category-slug',
            $this->parentCategory
        );

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->parentCategoryIsNotFromCategoryBranchValidator = new ParentCategoryIsNotFromCategoryBranchValidator(
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
