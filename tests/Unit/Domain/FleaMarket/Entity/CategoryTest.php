<?php

namespace Tests\Unit\Domain\FleaMarket\Entity;

use App\Domain\FleaMarket\Entity\Category;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\TestCase;

class CategoryTest extends TestCase
{
    private const TEST_TITLE = 'Test title';
    private const TEST_SLUG = 'test_slug';

    public function testCreateCategory(): void
    {
        $id = Uuid::uuid4();
        $category = $this->createCompany($id);

        $this->assertEquals($id, $category->getId());
        $this->assertEquals(self::TEST_TITLE, $category->getTitle());
        $this->assertEquals(self::TEST_SLUG, $category->getSlug());
    }

    private function createCompany(UuidInterface $id): Category
    {
        return new Category(
            $id,
            self::TEST_TITLE,
            self::TEST_SLUG
        );
    }
}
