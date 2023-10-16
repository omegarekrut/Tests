<?php

namespace Tests\Functional\Domain\FleaMarket\Command;

use App\Domain\FleaMarket\Command\UpdateCategoryCommand;
use App\Domain\FleaMarket\Entity\Category;
use Tests\DataFixtures\ORM\LoadFleaMarketCategories;
use Tests\Functional\ValidationTestCase;

/**
 * @group flea-market
 */
class UpdateCategoryCommandValidationTest extends ValidationTestCase
{
    private Category $categoryToUpdate;
    private Category $categoryFromAnotherBranch;
    private Category $subCategoryFromAnotherBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketCategories::class,
        ])->getReferenceRepository();

        $this->categoryToUpdate = $referenceRepository->getReference(LoadFleaMarketCategories::getReferenceRootName(LoadFleaMarketCategories::ROOT_VIDEO));
        $this->categoryFromAnotherBranch = $referenceRepository->getReference(LoadFleaMarketCategories::getReferenceRootName(LoadFleaMarketCategories::ROOT_TACKLE));
        $this->subCategoryFromAnotherBranch = $referenceRepository->getReference(LoadFleaMarketCategories::getRandChildReferenceNameByRootCategory(LoadFleaMarketCategories::ROOT_TACKLE));
    }

    protected function tearDown(): void
    {
        unset($this->categoryToUpdate, $this->categoryFromAnotherBranch, $this->subCategoryFromAnotherBranch);

        parent::tearDown();
    }

    public function testParentCategoryIsFromTheSameBranchAsCategoryToUpdate(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $command->parentCategory = $this->categoryToUpdate;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('parentCategory', 'Родительская категория не может быть дочерним элементом самой категории');
    }

    public function testInvalidTypeFields(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $command->parentCategory = 'test';
        $command->category = 'test';

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('parentCategory', 'Это поле должно иметь тип App\Domain\FleaMarket\Entity\Category');
        $this->assertFieldInvalid('category', 'Это поле должно иметь тип App\Domain\FleaMarket\Entity\Category');
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

    public function testSlugCannotContainsRussianSymbols(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);
        $command->slug = 'недопустимые символы';

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
    }

    public function testSlugCannotContainsNotAllowedSymbols(): void
    {
        $notAllowedSymbols = '.~:/?#[]@!$&\'()*+,;=';

        foreach (str_split($notAllowedSymbols) as $symbol) {
            $command = new UpdateCategoryCommand($this->categoryToUpdate);
            $command->slug = sprintf('some %s text', $symbol);

            $this->getValidator()->validate($command);

            $this->assertFieldInvalid('slug', 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
        }
    }

    public function testRootCategorySlugNotUnique(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->title = 'Test';
        $command->slug = LoadFleaMarketCategories::ROOT_ARTICLES;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL уже существующих категорий');
    }

    public function testChildCategorySlugNotUnique(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->parentCategory = $this->categoryFromAnotherBranch;
        $command->title = 'Test';
        $command->slug = $this->subCategoryFromAnotherBranch->getSlug();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL уже существующих категорий');
    }

    public function testChildCategorySlugEqualsRootCategorySlug(): void
    {
        $command = new UpdateCategoryCommand($this->categoryToUpdate);

        $command->parentCategory = $this->categoryFromAnotherBranch;
        $command->title = 'Test';
        $command->slug = $this->categoryFromAnotherBranch->getSlug();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL уже существующих категорий');
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
