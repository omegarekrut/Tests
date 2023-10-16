<?php

namespace Tests\Functional\Domain\Category\Command;

use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Entity\Category;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\Functional\ValidationTestCase;

/**
 * @group category
 */
class DeleteCategoryCommandValidationTest extends ValidationTestCase
{
    /** @var Category */
    private $parentCategoryWithRecords;
    /** @var Category */
    private $emptyChildCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
        ])->getReferenceRepository();

        /** @var Category $parentCategory */
        $parentCategory = $referenceRepository->getReference(sprintf('category-%s', LoadCategories::ROOT_ARTICLES));
        $this->parentCategoryWithRecords = $parentCategory->recordCountUp();

        $this->emptyChildCategory = $referenceRepository->getReference(LoadCategories::REFERENCE_ROOT_ARTICLE_TACKLE);
    }

    protected function tearDown(): void
    {
        unset($this->emptyChildCategory);
        unset($this->parentCategoryWithRecords);

        parent::tearDown();
    }

    public function testParentCategoryWithRecordsDelete(): void
    {
        $command = new DeleteCategoryCommand($this->parentCategoryWithRecords);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('category', 'Категории, имеющие дочерние категории, не подлежат удалению!');
        $this->assertFieldInvalid('category', 'Категории, имеющие дочерние записи, не подлежат удалению!');
    }

    public function testValidationShouldBePassedForEmptyChildCategoryDelete(): void
    {
        $command = new DeleteCategoryCommand($this->emptyChildCategory);

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
