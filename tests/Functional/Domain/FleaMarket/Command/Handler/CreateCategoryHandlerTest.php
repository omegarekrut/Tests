<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Handler;

use App\Domain\FleaMarket\Command\CreateCategoryCommand;
use App\Domain\FleaMarket\Entity\Category;
use Tests\DataFixtures\ORM\LoadFleaMarketCategories;
use Tests\Functional\TestCase;

/**
 * @group flea-market
 */
class CreateCategoryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketCategories::class,
        ])->getReferenceRepository();
        $parentCategory = $referenceRepository->getReference(LoadFleaMarketCategories::REFERENCE_ROOT_ARTICLE_TACKLE);

        $command = new CreateCategoryCommand();

        $command->parentCategory = $parentCategory;
        $command->title = 'Тестовая категория';
        $command->slug = 'test-category';

        $this->getCommandBus()->handle($command);

        $categoryRepository = $this->getEntityManager()->getRepository(Category::class);
        $categories = $categoryRepository->findBy(['slug' => $command->slug]);

        $this->assertCount(1, $categories);

        $category = $categories[0];

        $this->assertEquals($command->parentCategory, $category->getParent());
        $this->assertEquals($command->title, $category->getTitle());
        $this->assertEquals($command->slug, $category->getSlug());
    }
}
