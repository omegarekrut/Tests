<?php

namespace Tests\Functional\Domain\FleaMarket\Command;

use App\Domain\FleaMarket\Command\CreateCategoryCommand;
use Tests\DataFixtures\ORM\LoadFleaMarketCategories;
use Tests\Functional\ValidationTestCase;

/**
 * @group flea-market
 */
class CreateCategoryCommandValidationTest extends ValidationTestCase
{
    private CreateCategoryCommand $command;

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
        $this->assertOnlyFieldsAreInvalid($this->command, ['parentCategory'], 'test', 'Это поле должно иметь тип App\Domain\FleaMarket\Entity\Category');
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'slug'], null, 'Это поле обязательно для заполнения');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'slug'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов');
    }

    public function testSlugCannotContainsRussianSymbols(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['slug'], 'недопустимые символы', 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
    }

    public function testSlugCannotContainsNotAllowedSymbols(): void
    {
        $notAllowedSymbols = '.~:/?#[]@!$&\'()*+,;=';

        foreach (str_split($notAllowedSymbols) as $symbol) {
            $this->assertOnlyFieldsAreInvalid($this->command, ['slug'], sprintf('some %s text', $symbol), 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
        }
    }

    public function testValidationPassedWhenSlugNotUnique(): void
    {
        $this->loadFixtures([
            LoadFleaMarketCategories::class,
        ])->getReferenceRepository();

        $this->command->slug = LoadFleaMarketCategories::ROOT_ARTICLES;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', 'URL не должен совпадать с URL уже существующих категорий');
    }

    public function testValidationPassedForCorrectFilledCommand(): void
    {
        $this->command->title = 'Test';
        $this->command->slug = 'test-1_2';

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
