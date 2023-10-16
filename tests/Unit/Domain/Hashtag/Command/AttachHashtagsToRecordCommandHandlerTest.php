<?php

namespace Tests\Unit\Domain\Hashtag\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Command\AttachHashtagsToRecordCommand;
use App\Domain\Hashtag\Command\Handler\AttachHashtagsToRecordHandler;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Record\Common\Entity\Record;
use App\Module\Author\AuthorInterface;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class AttachHashtagsToRecordCommandHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $record = $this->getMockForAbstractClass(Record::class, [
            'title',
            'description',
            $this->createMock(AuthorInterface::class),
            $this->createMock(Category::class),
        ]);
        $hashtagCollection = $this->getHashtagCollection();

        $command = new AttachHashtagsToRecordCommand($record, $hashtagCollection);
        $handler = new AttachHashtagsToRecordHandler($objectManager);
        $handler->handle($command);

        /** @var Hashtag $hashtag */
        foreach ($hashtagCollection as $hashtag) {
            $this->assertTrue($record->isAttachedHashtag($hashtag));
        }
    }

    private function getHashtagCollection(): HashtagCollection
    {
        return new HashtagCollection([
            new Hashtag('рыбалка'),
            new Hashtag('зимняяРыбалка'),
            new Hashtag('летняяРыбалка'),
        ]);
    }
}
