<?php

namespace Tests\Unit\Domain\Record\Common\Collection;

use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Collection\RecordSemanticLinksFromSemanticLinkMatchesFactory;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Module\SemanticLink\SemanticLinkMatch;
use Tests\Unit\Mock\Entity\RecordMock;
use Tests\Unit\TestCase;

/**
 * @group record
 * @group semantic_link
 */
class RecordSemanticLinksFromSemanticLinkMatchesFactoryTest extends TestCase
{
    public function testCreateCollection(): void
    {
        $record = $this->createMock(RecordMock::class);
        $semanticLink = $this->createMock(SemanticLink::class);

        $semanticLinkMatch = new SemanticLinkMatch($semanticLink, 'мир прекрасен');

        $factory = new RecordSemanticLinksFromSemanticLinkMatchesFactory();

        $createdRecordSemanticLinkCollection = $factory->create($record, [$semanticLinkMatch]);

        $this->assertInstanceOf(RecordSemanticLinkCollection::class, $createdRecordSemanticLinkCollection);
        $this->assertNotEmpty($createdRecordSemanticLinkCollection);
        $this->assertContainsOnlyInstancesOf(RecordSemanticLink::class, $createdRecordSemanticLinkCollection);
    }
}
