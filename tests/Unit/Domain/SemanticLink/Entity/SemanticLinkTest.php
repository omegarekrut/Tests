<?php

namespace Tests\Unit\Domain\SemanticLink\Entity;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Module\Author\AuthorInterface;
use Ramsey\Uuid\Uuid;
use Tests\Unit\Mock\Entity\RecordMock;
use Tests\Unit\TestCase;

/**
 * @group semantic_link
 */
class SemanticLinkTest extends TestCase
{
    public function testGetRecord(): void
    {
        $semanticLink = $this->createSemanticLink();

        $this->assertEmpty($semanticLink->getSemanticLinkRecords());
    }

    public function testAttachUniqueRecord(): void
    {
        $semanticLink = $this->createSemanticLink();

        $recordUnique = $this->createRecord();

        $semanticLinkRecord = new RecordSemanticLink(Uuid::uuid4(), $recordUnique, $semanticLink, $semanticLink->getText());

        $semanticLink->attachUniqueSemanticLinkRecord($semanticLinkRecord);
        $semanticLink->attachUniqueSemanticLinkRecord($semanticLinkRecord);

        $this->assertNotEmpty($semanticLink->getSemanticLinkRecords());
        $this->assertCount(1, $semanticLink->getSemanticLinkRecords());
    }

    public function testDetachRecordSemanticLink(): void
    {
        $semanticLink = $this->createSemanticLink();

        $record = $this->createRecord();

        $semanticLinkRecord = new RecordSemanticLink(Uuid::uuid4(), $record, $semanticLink, $semanticLink->getText());


        $semanticLink->attachUniqueSemanticLinkRecord($semanticLinkRecord);
        $semanticLink->detachRecordSemanticLink($semanticLinkRecord);

        $this->assertEmpty($semanticLink->getSemanticLinkRecords());
    }

    private function createRecord(): Record
    {
        return new RecordMock(
            'Title',
            'description',
            $this->createMock(AuthorInterface::class)
        );
    }

    private function createSemanticLink(): SemanticLink
    {
        return new SemanticLink(
            Uuid::uuid4(),
            '/articles/view/88548',
            'description avoid message'
        );
    }
}
