<?php

namespace Tests\Functional\Domain\Category\Command\Handler;

use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Entity\Category;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\Functional\TestCase;

/**
 * @group category
 */
class CreateCategoryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();
        $parentCategory = $referenceRepository->getReference(LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE);

        $command = new CreateCategoryCommand();

        $command->parentCategory = $parentCategory;
        $command->title = 'Тестовая категория';
        $command->slug = 'test-category';
        $command->description = 'Описание тестовой категории';

        $this->getCommandBus()->handle($command);

        $categoryRepository = $this->getEntityManager()->getRepository(Category::class);
        $categories = $categoryRepository->findAllBySlug($command->slug);

        $this->assertCount(1, $categories);

        $category = $categories->first();

        $this->assertEquals($command->parentCategory, $category->getParent());
        $this->assertEquals($command->title, $category->getTitle());
        $this->assertEquals($command->slug, $category->getSlug());
        $this->assertEquals($command->description, $category->getDescription());
    }
}
