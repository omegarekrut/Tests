<?php

namespace Tests\Unit\Module\ArticleContents;

use App\Module\CanonicalUrl\CanonicalUrlCollection;
use Tests\Unit\TestCase;

/**
 * @group canonical-url
 */
class CanonicalUrlCollectionTest extends TestCase
{
    public function testCollectionCanBeCreateByUrls(): void
    {
        $expectedUrl = '//foo.bar';

        $collection = CanonicalUrlCollection::createByUrls(['http://foo.bar']);

        $this->assertCount(1, $collection);
        $this->assertEquals($expectedUrl, (string) $collection->first());
    }

    public function testCollectionsWithIntersectedUrlsMustDoIntersect(): void
    {
        $firstCollection = CanonicalUrlCollection::createByUrls(['http://foo.bar']);
        $secondCollection = CanonicalUrlCollection::createByUrls(['http://foo.bar']);

        $this->assertTrue($firstCollection->doIntersect($secondCollection));
    }

    public function testCollectionsWithoutIntersectedUrlsMustDontIntersect(): void
    {
        $firstCollection = CanonicalUrlCollection::createByUrls(['http://bar.foo']);
        $secondCollection = CanonicalUrlCollection::createByUrls(['http://foo.bar']);

        $this->assertFalse($firstCollection->doIntersect($secondCollection));
    }
}
