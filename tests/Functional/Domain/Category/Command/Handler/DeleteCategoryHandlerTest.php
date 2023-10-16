<?php

namespace Tests\Functional\Domain\Category\Command\Handler;

use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Entity\Category;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\Functional\TestCase;

/**
 * @group category
 */
class DeleteCategoryHandlerTest extends TestCase
{
    public function testCategoryIsDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();
        /** @var Category $categoryToDelete */
        $categoryToDelete = $referenceRepository->getReference(LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE);

        $command = new DeleteCategoryCommand($categoryToDelete);
        $this->getCommandBus()->handle($command);

        $this->getEntityManager()->clear();

        $categoryRepository = $this->getEntityManager()->getRepository(Category::class);
        $deletedCategories = $categoryRepository->findAllBySlug($categoryToDelete->getSlug());

        $this->assertEmpty($deletedCategories);
    }
}
