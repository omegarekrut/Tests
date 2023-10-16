<?php

namespace Tests\Functional\Domain\Record\Article\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Command\UpdateArticleCommand;
use App\Domain\Record\Article\Entity\Article;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\ValidationTestCase;

/**
 * @group article
 */
class UpdateArticleCommandValidationTest extends ValidationTestCase
{
    private UpdateArticleCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
            LoadArticles::class,
        ])->getReferenceRepository();

        $article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        assert($article instanceof Article);

        $category = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
        assert($category instanceof Category);

        $this->command = new UpdateArticleCommand($article);
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->authorIsOwnerCompany,
            $this->company,
        );

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->command->title = null;
        $this->command->text = null;
        $this->command->category = null;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Это поле обязательно для заполнения.');
        $this->assertFieldInvalid('text', 'Это поле обязательно для заполнения.');
        $this->assertFieldInvalid('category', 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->command->title = $this->getFaker()->realText(300);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Длина не должна превышать 255 символов.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $fakeText = mb_strtoupper($this->getFaker()->realText(50));
        $this->command->title = $fakeText;
        $this->command->text = $fakeText;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
        $this->assertFieldInvalid('text', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
