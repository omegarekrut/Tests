<?php

namespace Tests\Functional\Domain\FleaMarket\Command;

use App\Domain\FleaMarket\Command\DeleteCategoryCommand;
use App\Domain\FleaMarket\Entity\Category;
use Tests\DataFixtures\ORM\LoadFleaMarketCategories;
use Tests\Functional\ValidationTestCase;

/**
 * @group flea-market
 */
class DeleteCategoryCommandValidationTest extends ValidationTestCase
{
    private Category $parentCategory;
    private Category $emptyChildCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketCategories::class,
        ])->getReferenceRepository();

        /** @var Category $parentCategory */
        $this->parentCategory = $referenceRepository->getReference(sprintf('flea-market-category-%s', LoadFleaMarketCategories::ROOT_ARTICLES));

        $this->emptyChildCategory = $referenceRepository->getReference(LoadFleaMarketCategories::REFERENCE_ROOT_ARTICLE_TACKLE);
    }

    protected function tearDown(): void
    {
        unset(
            $this->emptyChildCategory,
            $this->parentCategoryWithRecords
        );

        parent::tearDown();
    }

    public function testValidationFailedForCategoryWithChildren(): void
    {
        $command = new DeleteCategoryCommand($this->parentCategory);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('category', 'Категории, имеющие дочерние категории, не подлежат удалению!');
    }

    public function testValidationPassedForCategoryWithoutChildren(): void
    {
        $command = new DeleteCategoryCommand($this->emptyChildCategory);

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
