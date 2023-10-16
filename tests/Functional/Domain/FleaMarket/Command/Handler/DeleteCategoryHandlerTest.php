<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Handler;

use App\Domain\FleaMarket\Command\DeleteCategoryCommand;
use App\Domain\FleaMarket\Entity\Category;
use App\Domain\FleaMarket\Repository\FleaMarketCategoryRepository;
use Tests\DataFixtures\ORM\LoadFleaMarketCategories;
use Tests\Functional\TestCase;

/**
 * @group flea-market
 */
class DeleteCategoryHandlerTest extends TestCase
{
    private FleaMarketCategoryRepository $categoryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->getContainer()->get(FleaMarketCategoryRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->categoryRepository);

        parent::tearDown();
    }

    public function testCategoryIsDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketCategories::class,
        ])->getReferenceRepository();

        /** @var Category $categoryToDelete */
        $categoryToDelete = $referenceRepository->getReference(LoadFleaMarketCategories::REFERENCE_ROOT_ARTICLE_TACKLE);

        $command = new DeleteCategoryCommand($categoryToDelete);
        $this->getCommandBus()->handle($command);

        $deletedCategories = $this->categoryRepository->findBySlug($categoryToDelete->getSlug());

        $this->assertEmpty($deletedCategories);
    }
}
