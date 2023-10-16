<?php

namespace Tests\Unit\Domain\Company\Collection;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use DateTime;
use DateTimeInterface;
use Tests\Unit\TestCase;

class StatisticsCollectionTest extends TestCase
{
    protected StatisticsCollection $statisticCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticCollection = new StatisticsCollection($this->getCompanyCardStatistics());
    }

    public function testGetGroupedDataByType(): void
    {
        $result = $this->statisticCollection->getGroupedByType();

        $this->assertCount(4, $result);
        $this->assertCount(2, $result[(string) StatisticsType::whatsappViews()]);
        $this->assertCount(2, $result[(string) StatisticsType::mapViews()]);
        $this->assertCount(2, $result[(string) StatisticsType::numberOfSubscriptions()]);
        $this->assertCount(1, $result[(string) StatisticsType::transitionsToCompanySocialMedia()]);
    }

    public function testMergeCollections(): void
    {
        $secondCollection = new StatisticsCollection($this->getCompanyCardStatistics());

        $mergedCollection = $this->statisticCollection->merge($secondCollection);

        $this->assertCount(14, $mergedCollection);
    }

    public function testGetSortedDataByDate(): void
    {
        $result = $this->statisticCollection->getSortedByDate();

        $this->assertCount(7, $result);

        $prevElement = null;

        foreach ($result as $element) {
            if ($prevElement === null) {
                $prevElement = $element;

                continue;
            }

            $this->assertLessThanOrEqual($element->getTrackingDate()->getTimestamp(), $prevElement->getTrackingDate()->getTimestamp());

            $prevElement = $element;
        }
    }

    /**
     * @return CompanyCardStatistics[]
     */
    private function getCompanyCardStatistics(): array
    {
        return [
            $this->getCompanyCardStatistic(StatisticsType::whatsappViews(), new DateTime('2022-01-01'), 1),
            $this->getCompanyCardStatistic(StatisticsType::whatsappViews(), new DateTime('2022-01-02'), 2),
            $this->getCompanyCardStatistic(StatisticsType::mapViews(), new DateTime('2022-01-01'), 4),
            $this->getCompanyCardStatistic(StatisticsType::mapViews(), new DateTime('2022-01-03'), 1),
            $this->getCompanyCardStatistic(StatisticsType::numberOfSubscriptions(), new DateTime('2022-01-03'), 1),
            $this->getCompanyCardStatistic(StatisticsType::numberOfSubscriptions(), new DateTime('2022-01-01'), 2),
            $this->getCompanyCardStatistic(StatisticsType::transitionsToCompanySocialMedia(), new DateTime('2022-01-01'), 2),
        ];
    }

    private function getCompanyCardStatistic(StatisticsType $type, DateTimeInterface $date, int $count): CompanyCardStatistics
    {
        $mock = $this->createMock(CompanyCardStatistics::class);

        $mock->method('getType')
            ->willReturn($type);

        $mock->method('getTrackingDate')
            ->willReturn($date);

        $mock->method('getCount')
            ->willReturn($count);

        return $mock;
    }
}
