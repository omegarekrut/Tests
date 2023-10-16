<?php

namespace Tests\Functional\Domain\Record\Tackle\Service;

use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Service\TackleBrandsService;
use Tests\Functional\RepositoryTestCase;

/**
 * @group tackleBrand
 */
class TackleBrandsServiceTest extends RepositoryTestCase
{
    /** @var TackleBrandsService */
    private $tackleBrandsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tackleBrandsService = $this->getContainer()->get(TackleBrandsService::class);
    }

    protected function tearDown(): void
    {
        unset($this->tackleBrandsService);

        parent::tearDown();
    }

    public function testGroupedPopularAndOtherBrands(): void
    {
        $tackleBrands = $this->createTackleBrands(13);

        $brands = $this->tackleBrandsService->groupedPopularAndOtherBrands($tackleBrands, '/tackles/rods/spinning/', '/tackles/rods/spinning/brand:major_craft/');

        $this->assertCount(2, $brands);
        $this->assertCount(10, $brands[0]);
        $this->assertCount(3, $brands[1]);

        $brands = $this->tackleBrandsService->groupedPopularAndOtherBrands($tackleBrands, '/tackles/rods/spinning/', '/tackles/rods/spinning/brand:title_1/');

        $this->assertCount(2, $brands);
        $this->assertCount(11, $brands[0]);
        $this->assertCount(2, $brands[1]);
    }

    public function testGroupedOnlyPopularBrands(): void
    {
        $tackleBrands = $this->createTackleBrands(2);

        $brands = $this->tackleBrandsService->groupedPopularAndOtherBrands($tackleBrands, '/tackles/rods/spinning/', '/tackles/rods/spinning/brand:major_craft/');

        $this->assertCount(2, $brands);
        $this->assertCount(2, $brands[0]);
        $this->assertCount(0, $brands[1]);
    }

    /**
     * @dataProvider checkingUrls
     */
    public function testGettingUrlWithoutBrands(string $expectedUrl, string $inputUrl): void
    {
        $this->assertEquals($expectedUrl, $this->tackleBrandsService->getUrlWithoutBrands($inputUrl));
    }

    /**
     * @return mixed[]
     */
    public function checkingUrls(): array
    {
        return [
            ['/tackles/rods/spinning/', '/tackles/rods/spinning/'],
            ['/tackles/rods/spinning/', '/tackles/rods/spinning/brand:major_craft/'],
            ['/tackles/', '/tackles/'],
            ['/tackles/', '/tackles/view/128396/'],
        ];
    }

    public function testGettingNumberOfBrands(): void
    {
        $tackleBrands = [
            ['countTackles' => 5],
            ['countTackles' => 10],
            ['countTackles' => 15],
        ];

        $this->assertEquals(30, $this->tackleBrandsService->getNumberOfBrands($tackleBrands));
    }

    /**
     * @return mixed[]
     */
    private function createTackleBrands(int $maxNumber): array
    {
        $tackleBrands = [];

        for ($i = 0; $i < $maxNumber; $i++) {
            $tackleBrands[] = [
                $this->createTackleBrand('title_'.$i),
                'countTackles' => $i + 1,
            ];
        }

        return $tackleBrands;
    }

    private function createTackleBrand(string $title): TackleBrand
    {
        $mock = $this->createMock(TackleBrand::class);

        $mock->method('getTitle')
            ->willReturn($title);

        $mock->method('getSlug')
            ->willReturn($title);

        return $mock;
    }
}
