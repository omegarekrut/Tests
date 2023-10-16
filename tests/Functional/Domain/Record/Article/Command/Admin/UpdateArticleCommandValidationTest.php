<?php

namespace Tests\Functional\Domain\Record\Article\Command\Admin;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Command\Admin\UpdateArticleCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

/**
 * @group article
 */
class UpdateArticleCommandValidationTest extends ValidationTestCase
{
    /** @var UpdateArticleCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UpdateArticleCommand($this->createMock(Article::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'text', 'priority', 'category'], null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['preview'], $this->getFaker()->realText(350), 'Длина не должна превышать 300 символов.');
    }

    public function testInvalidTypeFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['priority'], 'test', 'Это значение должно быть числом.');
    }

    public function testOnMinValue(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['priority'], -1, 'Приоритет не может быть меньше 0');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->category = $this->createMock(Category::class);
        $this->command->title = 'Article title created';
        $this->command->preview = 'Article preview';
        $this->command->text = 'Article text';
        $this->command->priority = 6;
        $this->command->images = new ImageCollection([new Image('image.jpg')]);

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
