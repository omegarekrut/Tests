<?php

namespace Tests\Functional\Domain\Category\Command;

use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\Entity\Category;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\Functional\ValidationTestCase;

/**
 * @group category
 */
class UpdateCategoryCommandValidationTest extends ValidationTestCase
{
    /** @var Category */
    private $categoryToUpdate;
    /** @var Category */
    private $categoryFromAnotherBranch;
    /** @var Category */
    private $subCategoryFromAnotherBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();

        $this->categoryToUpdate = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_VIDEO));
        $this->categoryFromAnotherBranch = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_TACKLE));
        $this->subCategoryFromAnotherBranch = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_TACKLE));
    }

    protected function tearDown(): void
    {
        unset($this->categoryToUpdate, $this->categoryFromAnotherBranch);

        parent::tearDown();
    }

    public function testParentCategoryIsFromTheSameBranchAsCategoryToUpdate(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $command->parentCategory = $this->categoryToUpdate;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('parentCategory', 'Родительская категория не может быть из той же ветви, что сама категория');
    }

    public function testInvalidTypeFields(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $command->parentCategory = 'test';
        $command->category = 'test';

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('parentCategory', 'Это поле должно иметь тип App\Domain\Category\Entity\Category');
        $this->assertFieldInvalid('category', 'Это поле должно иметь тип App\Domain\Category\Entity\Category');
    }

    public function testNotBlankFields(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $this->assertOnlyFieldsAreInvalid($command, ['category', 'title', 'slug'], null, 'Это поле обязательно для заполнения');
    }

    public function testInvalidLengthFields(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $longText = $this->getFaker()->realText(300);
        $command->title = $longText;
        $command->slug = $longText;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('title', 'Длина не должна превышать 255 символов');
        $this->assertFieldInvalid('slug', 'Длина не должна превышать 255 символов');
    }

    public function testSlugCannotContainsNotAllowedSymbols(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $command->slug = 'недопустимые символы';

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
    }

    public function testRootCategorySlugNotUnique(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->title = 'Test';
        $command->slug = Category::ROOT_ARTICLES_SLUG;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL корневых категорий');
    }

    public function testChildCategorySlugNotUnique(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->parentCategory = $this->categoryFromAnotherBranch;
        $command->title = 'Test';
        $command->slug = $this->subCategoryFromAnotherBranch->getSlug();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL дочерних категорий корневой категории');
    }

    public function testChildCategorySlugEqualsRootCategorySlug(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->parentCategory = $this->categoryFromAnotherBranch;
        $command->title = 'Test';
        $command->slug = $this->categoryFromAnotherBranch->getSlug();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL родительской корневой категории');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->parentCategory = $this->categoryFromAnotherBranch;
        $command->title = 'Test';
        $command->slug = 'test-1_2';

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
