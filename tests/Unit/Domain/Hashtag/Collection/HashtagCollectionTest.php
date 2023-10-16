<?php

namespace Tests\Unit\Domain\Hashtag\Collection;

use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Record\Common\Entity\Record;
use Tests\Unit\TestCase;

class HashtagCollectionTest extends TestCase
{
    public function testCollectionCanFilterAttachedToRecord(): void
    {
        $hashtags = new HashtagCollection([
            $firstExpectedHashtag = $this->createNotAttachedToAnyRecordHashtag(),
            $this->createAttachedToAnyRecordHashtag(),
            $secondExpectedHashtag = $this->createNotAttachedToAnyRecordHashtag(),
        ]);

        $notAttachedHashtags = $hashtags->getNotAttachedToRecord($this->createMock(Record::class));

        $this->assertCount(2, $notAttachedHashtags);
        $this->assertContains($firstExpectedHashtag, $notAttachedHashtags);
        $this->assertContains($secondExpectedHashtag, $notAttachedHashtags);
    }

    public function testCollectionCanFilterExistsInNames(): void
    {
        $hashtags = new HashtagCollection([
            $this->createHashtagWithName('рыбалка'),
            $firstExpectedHashtag = $this->createHashtagWithName('рыбка'),
            $this->createHashtagWithName('зимняяРыбалка'),
            $secondExpectedHashtag = $this->createHashtagWithName('летняяРыбалка'),
        ]);

        $notAttachedHashtags = $hashtags->getNotExistsInNames(['рыбалка', 'зимняяРыбалка']);

        $this->assertCount(2, $notAttachedHashtags);
        $this->assertContains($firstExpectedHashtag, $notAttachedHashtags);
        $this->assertContains($secondExpectedHashtag, $notAttachedHashtags);
    }

    public function testCollectionCanCheckIfExistsHashtagWithSameName(): void
    {
        $hashtags = new HashtagCollection([
            $this->createHashtagWithName('рыбалка'),
            $this->createHashtagWithName('зимняяРыбалка'),
            $this->createHashtagWithName('летняяРыбалка'),
        ]);

        $this->assertTrue($hashtags->existsWithSameName('рыбалка'));
        $this->assertFalse($hashtags->existsWithSameName('рыбка'));
    }

    public function testCollectionCanItselfWithAnother(): void
    {
        $firstHashtagCollection = new HashtagCollection([
            $this->createHashtagWithName('рыбалка'),
            $this->createHashtagWithName('зимняяРыбалка'),
        ]);

        $secondHashtagCollection = new HashtagCollection([
            $this->createHashtagWithName('летняяРыбалка'),
            $this->createHashtagWithName('рыбка'),
        ]);

        $this->assertCount(4, $firstHashtagCollection->merge($secondHashtagCollection));
    }

    private function createNotAttachedToAnyRecordHashtag(): Hashtag
    {
        $stub = $this->createMock(Hashtag::class);
        $stub
            ->method('isAttachedRecord')
            ->willReturn(false);

        return $stub;
    }

    private function createAttachedToAnyRecordHashtag(): Hashtag
    {
        $stub = $this->createMock(Hashtag::class);
        $stub
            ->method('isAttachedRecord')
            ->willReturn(true);

        return $stub;
    }

    private function createHashtagWithName(string $name): Hashtag
    {
        $stub = $this->createMock(Hashtag::class);
        $stub
            ->method('getName')
            ->willReturn($name);

        return $stub;
    }
}
