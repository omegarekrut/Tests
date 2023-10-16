<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Rss\Record\PartitionConverter\Common as CommonRecordInformationConverter;
use App\Module\Author\AuthorInterface;
use DateTime;

/**
 * @group rss
 */
class CommonTest extends TestCase
{
    private const EXPECTED_RECORD_VIEW_URL = 'http://site.com/record/view/url';

    /** @var CommonRecordInformationConverter */
    private $commonRecordInformationConverter;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var RecordViewUrlGenerator $recordViewUrlGenerator */
        $recordViewUrlGenerator = $this->createConfiguredMock(RecordViewUrlGenerator::class, [
            'generate' => self::EXPECTED_RECORD_VIEW_URL,
        ]);

        $this->commonRecordInformationConverter = new CommonRecordInformationConverter($recordViewUrlGenerator);
    }

    protected function tearDown(): void
    {
        unset($this->commonRecordInformationConverter);

        parent::tearDown();
    }

    public function testRecordTitleMustBeConvertedAsItemTitle(): void
    {
        /** @var Record $record */
        $record = $this->createConfiguredMock(Record::class, [
            'getTitle' => 'Record title',
        ]);

        $this->assertEquals($record->getTitle(), $this->commonRecordInformationConverter->convertTitle($record));
    }

    public function testRecordViewUrlMustBeConvertedAsItemGuidAndLink(): void
    {
        $record = $this->createMock(Record::class);

        $this->assertEquals(self::EXPECTED_RECORD_VIEW_URL, $this->commonRecordInformationConverter->convertGuid($record));
        $this->assertEquals(self::EXPECTED_RECORD_VIEW_URL, $this->commonRecordInformationConverter->convertLink($record));
    }

    public function testRecordAuthorNameMustBeConvertedAsItemAuthor(): void
    {
        /** @var AuthorInterface $author */
        $author = $this->createConfiguredMock(AuthorInterface::class, [
            'getUsername' => 'Author name',
        ]);

        /** @var Record $record */
        $record = $this->createConfiguredMock(Record::class, [
            'getAuthor' => $author,
        ]);

        $this->assertEquals($record->getAuthor()->getUsername(), $this->commonRecordInformationConverter->convertAuthor($record));
    }

    public function testRecordCreatedAtBeConvertedAsItemPublishedDate(): void
    {
        $expectedPublishedDate = new DateTime();

        /** @var Record $record */
        $record = $this->createConfiguredMock(Record::class, [
            'getCreatedAt' => $expectedPublishedDate,
        ]);

        $this->assertEquals($expectedPublishedDate, $this->commonRecordInformationConverter->convertPublicDate($record));
    }
}
