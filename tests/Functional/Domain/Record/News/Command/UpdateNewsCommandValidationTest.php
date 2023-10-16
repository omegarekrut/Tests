<?php

namespace Tests\Functional\Domain\Record\News\Command;

use App\Domain\Record\News\Command\UpdateNewsCommand;
use App\Domain\Record\News\Entity\News;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

/**
 * @group news
 */
class UpdateNewsCommandValidationTest extends ValidationTestCase
{
    private UpdateNewsCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UpdateNewsCommand($this->createMock(News::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $requiredFields = ['title', 'preview', 'text', 'priority', 'image'];

        $this->assertOnlyFieldsAreInvalid($this->command, $requiredFields, null, 'Это поле обязательно для заполнения.');
    }

    public function testPreviewContainTooMuchUpperCase(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['preview'], mb_strtoupper($this->getFaker()->realText(50)), 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
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
        $this->command->title = 'title';
        $this->command->preview = 'preview';
        $this->command->image = $this->createMock(Image::class);
        $this->command->text = 'text';
        $this->command->priority = 1;
        $this->command->actual = $this->createMock(\DateTime::class);

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
