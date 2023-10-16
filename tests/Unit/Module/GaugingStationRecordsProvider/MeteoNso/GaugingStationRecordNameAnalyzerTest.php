<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\MeteoNso;

use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordNameAnalyzer;
use Tests\Unit\TestCase;

class GaugingStationRecordNameAnalyzerTest extends TestCase
{
    /** @var GaugingStationRecordNameAnalyzer */
    private $gaugingStationRecordNameAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gaugingStationRecordNameAnalyzer = new GaugingStationRecordNameAnalyzer();
    }

    protected function tearDown(): void
    {
        unset($this->gaugingStationRecordNameAnalyzer);

        parent::tearDown();
    }

    /**
     * @dataProvider getKnownNotAnalyzableRecordNamesWithResult
     */
    public function testKnownNotAnalyzableRecordNamesCanBeAnalyzed(string $recordName, array $expectedAnalysisResult): void
    {
        $analysisResult = $this->gaugingStationRecordNameAnalyzer->analyze($recordName);

        $this->assertSame($expectedAnalysisResult, $analysisResult);
    }

    public function getKnownNotAnalyzableRecordNamesWithResult(): \Generator
    {
        yield [
            'recordName' => 'вдхр',
            'expectedAnalysisResult' => ['вдхр', 'Обское водохранилище', null],
        ];

        yield [
            'recordName' => 'Верхний бьеф (вдхр)',
            'expectedAnalysisResult' => ['вдхр', 'Обское водохранилище', 'верхний бьеф'],
        ];

        yield [
            'recordName' => 'Нижний бьеф (р.Обь)',
            'expectedAnalysisResult' => ['р', 'Обь', 'нижний бьеф'],
        ];

        yield [
            'recordName' => 'р. Бердь',
            'expectedAnalysisResult' => ['р', 'Бердь', null],
        ];
    }

    public function testNotAnalyzableRecordNamesCanNotBeAnalyzed(): void
    {
        $analysisResult = $this->gaugingStationRecordNameAnalyzer->analyze('not analyzable record name');

        $this->assertSame([null, null, null], $analysisResult);
    }

    public function testRecordNameCanContainOnlyWaterNameAndType(): void
    {
        $analysisResult = $this->gaugingStationRecordNameAnalyzer->analyze('type.Water name');

        $this->assertSame(['type', 'Water name', null], $analysisResult);
    }

    public function testAdditionallyRecordNameCanContainSensorName(): void
    {
        $analysisResult = $this->gaugingStationRecordNameAnalyzer->analyze('type.Water name (sensor.name)');

        $this->assertSame(['type', 'Water name', 'sensor.name'], $analysisResult);
    }
}
