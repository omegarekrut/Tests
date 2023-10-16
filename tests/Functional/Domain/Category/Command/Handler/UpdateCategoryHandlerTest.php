<?php

namespace Tests\Functional\Domain\Category\Command\Handler;

use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\Entity\Category;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\Functional\TestCase;

/**
 * @group category
 */
class UpdateCategoryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();
        /** @var Category $parentCategory */
        $parentCategory = $referenceRepository->getReference(sprintf('category-%s', LoadCategories::ROOT_ARTICLES));
        /** @var Category $categoryToUpdate */
        $categoryToUpdate = $referenceRepository->getReference(LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE);

        $command = new UpdateCategoryCommand($categoryToUpdate);

        $command->parentCategory = $parentCategory;
        $command->title = 'Новое название категории';
        $command->slug = 'updated-category';
        $command->description = 'Новое описание тестовой категории';

        $this->getCommandBus()->handle($command);
        $updatedCategory = $categoryToUpdate;

        $this->assertEquals($command->parentCategory, $updatedCategory->getParent());
        $this->assertEquals($command->title, $updatedCategory->getTitle());
        $this->assertEquals($command->slug, $updatedCategory->getSlug());
        $this->assertEquals($command->description, $updatedCategory->getDescription());
    }
}
