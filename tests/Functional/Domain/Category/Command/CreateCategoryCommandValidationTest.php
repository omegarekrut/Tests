<?php

namespace Tests\Functional\Domain\Category\Command;

use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Entity\Category;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\Functional\ValidationTestCase;

/**
 * @group category
 */
class CreateCategoryCommandValidationTest extends ValidationTestCase
{
    /** @var CreateCategoryCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->command = new CreateCategoryCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testInvalidTypeFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['parentCategory'], 'test', 'Это поле должно иметь тип App\Domain\Category\Entity\Category');
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'slug'], null, 'Это поле обязательно для заполнения');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'slug'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов');
    }

    public function testSlugCannotContainsNotAllowedSymbols(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['slug'], 'недопустимые символы', 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
    }

    public function testRootCategorySlugNotUnique(): void
    {
        $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();

        $this->command->slug = LoadCategories::ROOT_ARTICLES;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL корневых категорий');
    }

    public function testChildCategorySlugNotUnique(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();

        /** @var Category $parentCategory */
        $parentCategory = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_ARTICLES));
        /** @var Category $childCategory */
        $childCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));

        $this->command->parentCategory = $parentCategory;
        $this->command->slug = $childCategory->getSlug();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL дочерних категорий корневой категории');
    }

    public function testChildCategorySlugEqualsRootCategorySlug(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();

        /** @var Category $parentCategory */
        $parentCategory = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_ARTICLES));
        /** @var Category $childCategory */
        $childCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));

        $this->command->parentCategory = $childCategory;
        $this->command->slug = $parentCategory->getSlug();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL родительской корневой категории');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->title = 'Test';
        $this->command->slug = 'test-1_2';

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
