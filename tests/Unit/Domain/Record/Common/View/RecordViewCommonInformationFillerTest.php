<?php

namespace Tests\Unit\Domain\Record\Common\View;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Module\Author\View\AuthorView;
use App\Module\Author\View\AuthorViewFactory;
use App\Module\Voting\Entity\VotableIdentifier;
use Carbon\Carbon;
use DateTime;
use Tests\Unit\Mock\View\RecordViewMock;
use Tests\Unit\TestCase;

class RecordViewCommonInformationFillerTest extends TestCase
{
    private RecordViewCommonInformationFiller $recordViewCommonInformationFilter;

    private RecordViewMock $recordView;

    protected function setUp(): void
    {
        parent::setUp();

        $recordAuthorViewFactory = $this->createMock(AuthorViewFactory::class);
        $recordAuthorViewFactory->method('createFromHasCompanyAuthorInterface')
            ->willReturn(new AuthorView());

        $this->recordViewCommonInformationFilter = new RecordViewCommonInformationFiller(
            $recordAuthorViewFactory
        );
        $this->recordView = new RecordViewMock();
    }

    protected function tearDown(): void
    {
        unset(
            $this->recordViewCommonInformationFilter,
            $this->recordView
        );

        parent::tearDown();
    }

    public function testFillUpdatedAtWhenItIsEqualsCreatedAt(): void
    {
        $date = new DateTime();
        $record = $this->createRecordWithDates($date, $date);

        $this->recordViewCommonInformationFilter->fill($this->recordView, $record);

        $this->assertNull($this->recordView->updatedAt);
    }

    public function testFillUpdatedAtWhenItIsSameDayAsCreatedAt(): void
    {
        $record = $this->createRecordWithDates(
            Carbon::parse('2020-12-20 22:00'),
            Carbon::parse('2020-12-21 21:59')
        );

        $this->recordViewCommonInformationFilter->fill($this->recordView, $record);

        $this->assertNull($this->recordView->updatedAt);
    }

    public function testFillUpdatedAtWhenItIsOneDayMoreCreatedAt(): void
    {
        $record = $this->createRecordWithDates(
            Carbon::parse('2020-12-20 22:00'),
            Carbon::parse('2020-12-21 22:00')
        );

        $this->recordViewCommonInformationFilter->fill($this->recordView, $record);

        $this->assertInstanceOf(DateTime::class, $this->recordView->updatedAt);
    }

    private function createRecordWithDates(DateTime $createdAt, ?DateTime $updatedAt = null): Record
    {
        $record = $this->createMock(Record::class);
        $record
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier(1, 'type'));
        $record
            ->method('getCreatedAt')
            ->willReturn($createdAt);
        $record
            ->method('getUpdatedAt')
            ->willReturn($updatedAt);

        return $record;
    }
}
