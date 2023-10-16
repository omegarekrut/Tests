<?php

namespace Tests\Functional\Domain\Page\Command;

use App\Domain\Page\Command\CreatePageCommand;
use Tests\DataFixtures\ORM\LoadPages;
use Tests\Functional\ValidationTestCase;

/**
 * @group page
 */
class CreatePageCommandValidationTest extends ValidationTestCase
{
    /**
     * @var CreatePageCommand
     */
    private $command;
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadPages::class,
        ])->getReferenceRepository();

        $this->command = new CreatePageCommand();
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->command
        );

        parent::tearDown();
    }

    public function testNotUniqueSlug(): void
    {
        $page = $this->referenceRepository->getReference(LoadPages::getReferenceName(LoadPages::PAGE_ABOUT));
        $this->command->slug = $page->getSlug();
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', 'Страница с выбранным url \'about\' уже существует.');
    }

    public function testInvalidSlugFormat(): void
    {
        $this->command->slug = $this->getFaker()->name;
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', 'Разрешены только латинские буквы, цифры, \'-\' и \'_\'.');
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['slug', 'title', 'text'], null, 'Значение не должно быть пустым.');
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['slug', 'title'], $this->getFaker()->realText(500), 'Значение слишком длинное. Должно быть равно 128 символам или меньше.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['metaTitle'], $this->getFaker()->realText(500), 'Значение meta title слишком длинное. Должно быть равно 255 символам или меньше.');
    }
}
