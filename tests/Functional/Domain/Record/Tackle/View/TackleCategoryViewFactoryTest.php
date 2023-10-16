<?php

namespace Tests\Functional\Domain\Record\Tackle\View;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\View\CategoryView;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Service\TackleUrlGenerator;
use App\Domain\Record\Tackle\View\TackleCategoryViewFactory;
use App\Domain\Statistic\Repository\TacklesCountInCategoriesReportRepository;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadTackleBrands;
use Tests\Functional\TestCase;

/**
 * @group tackleCategories
 */
class TackleCategoryViewFactoryTest extends TestCase
{
    /** @var Category */
    private $rootTackleCategory;

    /** @var TackleBrand */
    private $tackleBrand;

    /** @var TackleCategoryViewFactory */
    private $tackleCategoryViewFactory;

    /** @var TackleUrlGenerator */
    private $tackleUrlGenerator;

    /** @var TacklesCountInCategoriesReportRepository */
    private $tacklesCountInCategoriesReportRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
            LoadTackleBrands::class,
        ])->getReferenceRepository();

        $this->rootTackleCategory = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_TACKLE));
        $this->tackleBrand = $referenceRepository->getReference(LoadTackleBrands::getRandReferenceName());

        $this->tackleCategoryViewFactory = $this->getContainer()->get(TackleCategoryViewFactory::class);
        $this->tackleUrlGenerator = $this->getContainer()->get(TackleUrlGenerator::class);
        $this->tacklesCountInCategoriesReportRepository = $this->getContainer()->get(TacklesCountInCategoriesReportRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->rootTackleCategory,
            $this->tackleBrand,
            $this->tackleCategoryViewFactory,
            $this->tackleUrlGenerator,
            $this->tacklesCountInCategoriesReportRepository
        );

        parent::tearDown();
    }

    public function testCategoryViewCanBeCreatedWithCommonDataFromCategory(): void
    {
        $categoryView = $this->tackleCategoryViewFactory->create($this->rootTackleCategory, null);

        $this->assertEquals($this->rootTackleCategory->getTitle(), $categoryView->title);
        $this->assertEquals($this->rootTackleCategory->getId(), $categoryView->id);
    }

    public function testViewUrlMustBeGeneratedByUrlGenerator(): void
    {
        $expectedViewUrl = $this->tackleUrlGenerator->generateTackleUrl($this->rootTackleCategory);

        $categoryView = $this->tackleCategoryViewFactory->create($this->rootTackleCategory, null);

        $this->assertEquals($expectedViewUrl, $categoryView->viewUrl);
    }

    public function testViewUrlMustBeGeneratedGivenBrand(): void
    {
        $expectedViewUrl = $this->tackleUrlGenerator->generateTackleUrl($this->rootTackleCategory, $this->tackleBrand);

        $categoryView = $this->tackleCategoryViewFactory->create($this->rootTackleCategory, $this->tackleBrand);

        $this->assertEquals($expectedViewUrl, $categoryView->viewUrl);
    }

    public function testRecordsCountMustBeCalculatedByCommonCountInCategoryReport(): void
    {
        $report = $this->tacklesCountInCategoriesReportRepository->getCommonReport();

        $categoryView = $this->tackleCategoryViewFactory->create($this->rootTackleCategory, null);

        $this->assertEquals($report->getCountInCategory($this->rootTackleCategory), $categoryView->recordsCount);
    }

    public function testRecordsCountMustBeCalculatedGivenBrand(): void
    {
        $report = $this->tacklesCountInCategoriesReportRepository->getReportByBrand($this->tackleBrand);

        $categoryView = $this->tackleCategoryViewFactory->create($this->rootTackleCategory, $this->tackleBrand);

        $this->assertEquals($report->getCountInCategory($this->rootTackleCategory), $categoryView->recordsCount);
    }

    public function testCategoryViewMustContainsNestedCategoryViews(): void
    {
        $expectedChildrenCount = count($this->rootTackleCategory->getChildren());

        $categoryView = $this->tackleCategoryViewFactory->create($this->rootTackleCategory, null);

        $this->assertGreaterThan(0, $expectedChildrenCount);
        $this->assertCount($expectedChildrenCount, $categoryView->children);
        $this->assertInstanceOf(CategoryView::class, current($categoryView->children));
    }
}
