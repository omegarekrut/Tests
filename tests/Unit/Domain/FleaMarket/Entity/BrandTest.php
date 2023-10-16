<?php

namespace Tests\Unit\Domain\FleaMarket\Entity;

use App\Domain\FleaMarket\Entity\Brand;
use App\Domain\FleaMarket\Entity\ValueObject\LogoImage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\TestCase;

class BrandTest extends TestCase
{
    private const TEST_TITLE = 'Test title';
    private const TEST_SLUG = 'test-slug';
    private const TEST_DESCRIPTION = 'Test description';

    public function testCreateBrand(): void
    {
        $id = Uuid::uuid4();
        $brand = $this->createBrand($id);

        $this->assertEquals($id, $brand->getId());
        $this->assertEquals(self::TEST_TITLE, $brand->getTitle());
        $this->assertEquals(self::TEST_SLUG, $brand->getSlug());
        $this->assertEquals(self::TEST_DESCRIPTION, $brand->getDescription());
        $this->assertInstanceOf(LogoImage::class, $brand->getLogoImage());
    }

    private function createBrand(UuidInterface $id): Brand
    {
        return new Brand(
            $id,
            self::TEST_TITLE,
            self::TEST_DESCRIPTION,
            self::TEST_SLUG
        );
    }
}
