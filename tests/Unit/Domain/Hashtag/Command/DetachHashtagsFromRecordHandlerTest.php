<?php

namespace Tests\Unit\Domain\Hashtag\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Command\DetachHashtagsFromRecordCommand;
use App\Domain\Hashtag\Command\Handler\DetachHashtagsFromRecordHandler;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Record\Common\Entity\Record;
use App\Module\Author\AuthorInterface;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class DetachHashtagsFromRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $recordWithFirstHashtag = $this->getMockForAbstractClass(Record::class, [
            'title',
            'description',
            $this->createMock(AuthorInterface::class),
            $this->createMock(Category::class),
        ]);

        $attachedHashtagToTwoRecords = new Hashtag('зимняяРыбалка');
        $recordWithFirstHashtag->addHashtag($attachedHashtagToTwoRecords);

        $hashtagCollection = new HashtagCollection([
            $notAttachedToAnyRecordsHashtag = new Hashtag('рыбалка'),
            $attachedHashtagToTwoRecords,
        ]);

        $record = $this->getMockForAbstractClass(Record::class, [
            'title',
            'description',
            $this->createMock(AuthorInterface::class),
            $this->createMock(Category::class),
        ]);

        /** @var Hashtag $hashtag */
        foreach ($hashtagCollection as $hashtag) {
            $record->addHashtag($hashtag);
        }

        $command = new DetachHashtagsFromRecordCommand($record, $hashtagCollection);
        $handler = new DetachHashtagsFromRecordHandler($objectManager);
        $handler->handle($command);

        foreach ($hashtagCollection as $hashtag) {
            $this->assertFalse($hashtag->isAttachedRecord($record));
        }

        $this->assertTrue($objectManager->isRemoved($notAttachedToAnyRecordsHashtag));
        $this->assertFalse($objectManager->isRemoved($attachedHashtagToTwoRecords));
    }
}
