<?php

namespace Tests\Unit\Domain\Comment\Collection;

use App\Domain\Comment\Collection\AnswerCollection;
use App\Domain\Comment\Entity\Comment;
use Tests\Unit\TestCase;

/**
 * @group comment
 */
class AnswerCollectionTest extends TestCase
{
    private const COUNT_COMMENTS_IN_COLLECTION = 3;
    private const COUNT_COMMENTS_IN_EXTENDED_COLLECTION = self::COUNT_COMMENTS_IN_COLLECTION * 2;

    public function testCreateByMergeCollectionIncreasesTheCount(): void
    {
        $collection = $this->getCollectionWithElements();
        $this->assertCount(self::COUNT_COMMENTS_IN_COLLECTION, $collection);

        $extendedCollection = $collection->createByMerge($this->getCollectionWithElements());
        $this->assertCount(self::COUNT_COMMENTS_IN_EXTENDED_COLLECTION, $extendedCollection);
    }

    public function testCreateByMerge(): void
    {
        $firstCollection = $this->getCollectionWithElements();
        $secondCollection = $this->getCollectionWithElements();

        $extendedCollection = $firstCollection->createByMerge($secondCollection);

        foreach ($firstCollection as $comment) {
            $this->assertTrue($extendedCollection->contains($comment));
        }

        foreach ($secondCollection as $comment) {
            $this->assertTrue($extendedCollection->contains($comment));
        }

        $this->assertCount($firstCollection->count() + $secondCollection->count(), $extendedCollection);
    }

    private function getCommentMock(): Comment
    {
        return $this->createMock(Comment::class);
    }

    private function getCollectionWithElements(): AnswerCollection
    {
        $collection = new AnswerCollection();

        for ($i = 0; $i < self::COUNT_COMMENTS_IN_COLLECTION; $i++) {
            $collection->add($this->getCommentMock());
        }

        return $collection;
    }
}
