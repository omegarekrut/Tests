<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Handler;

use App\Domain\FleaMarket\Command\UpdateCategoryCommand;
use App\Domain\FleaMarket\Entity\Category;
use Tests\DataFixtures\ORM\LoadFleaMarketCategories;
use Tests\Functional\TestCase;

/**
 * @group flea-market
 */
class UpdateCategoryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketCategories::class,
        ])->getReferenceRepository();
        /** @var Category $parentCategory */
        $parentCategory = $referenceRepository->getReference(sprintf('flea-market-category-%s', LoadFleaMarketCategories::ROOT_ARTICLES));
        /** @var Category $categoryToUpdate */
        $categoryToUpdate = $referenceRepository->getReference(LoadFleaMarketCategories::REFERENCE_ROOT_ARTICLE_TACKLE);

        $command = new UpdateCategoryCommand($categoryToUpdate);

        $command->parentCategory = $parentCategory;
        $command->title = 'Новое название категории';
        $command->slug = 'updated-category';

        $this->getCommandBus()->handle($command);
        $updatedCategory = $categoryToUpdate;

        $this->assertEquals($command->parentCategory, $updatedCategory->getParent());
        $this->assertEquals($command->title, $updatedCategory->getTitle());
        $this->assertEquals($command->slug, $updatedCategory->getSlug());
    }
}
