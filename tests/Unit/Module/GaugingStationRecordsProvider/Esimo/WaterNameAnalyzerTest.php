<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\Esimo;

use App\Module\GaugingStationRecordsProvider\Esimo\Exception\WaterNameIsEmptyException;
use App\Module\GaugingStationRecordsProvider\Esimo\Exception\WaterTypeIsNotSupportedException;
use App\Module\GaugingStationRecordsProvider\Esimo\WaterNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType;
use Tests\Unit\TestCase;

class WaterNameAnalyzerTest extends TestCase
{
    private const VALID_HYDROLOGICAL_KNOWLEDGE_CODE = '1';

    private WaterNameAnalyzer $nameAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nameAnalyzer = new WaterNameAnalyzer();
    }

    protected function tearDown(): void
    {
        unset($this->nameAnalyzer);

        parent::tearDown();
    }

    /**
     * @dataProvider getNames
     */
    public function testAnalyze(string $waterName, string $gaugingStationName, string $expectedAnalyzedWaterName, WaterType $expectedWaterType): void
    {
        [$waterType, $analyzedWaterName] = $this->nameAnalyzer->analyze($waterName, $gaugingStationName, self::VALID_HYDROLOGICAL_KNOWLEDGE_CODE);

        $this->assertEquals($expectedAnalyzedWaterName, $analyzedWaterName);
        $this->assertEquals($expectedWaterType, $waterType);
    }

    /**
     * @return mixed[]
     */
    public function getNames(): array
    {
        return [
            ['Р.СУРА', '', 'Сура', WaterType::river()],
            ['P.HEMAH(HЯMУHAC)', '', 'Неман(Нямунас)', WaterType::river()],
            ['Р.СУХОНА  БАСС.Р.СЕВЕРНАЯ ДВИНА', '', 'Сухона', WaterType::river()],
            ['МОРЕ БЕЛОЕ  P.MEЗEHЬ', '', 'Мезень', WaterType::river()],
            ['КАН. НОВОЛАДОЖСКИЙ (НОВО-СВИРСКИЙ)', '', 'Новоладожский (Ново-Свирский)', WaterType::channel()],
            ['KAHAЛ CTAPO-ЛАДОЖСКИЙ', '', 'Старо-Ладожский', WaterType::channel()],
            ['', 'г.Гомель,река Сож', 'Сож', WaterType::river()],
            ['Р. ТЕРЕК', '', 'Терек', WaterType::river()],
            ['Р.ТЕРЕК', '', 'Терек', WaterType::river()],
            ['р.k', '', 'К', WaterType::river()],
            ['р.a', '', 'А', WaterType::river()],
            ['р.c', '', 'С', WaterType::river()],
            ['р.p', '', 'Р', WaterType::river()],
            ['р.t', '', 'Т', WaterType::river()],
            ['р.m', '', 'М', WaterType::river()],
            ['р.b', '', 'В', WaterType::river()],
            ['р.e', '', 'Е', WaterType::river()],
            ['р.o', '', 'О', WaterType::river()],
            ['р.x', '', 'Х', WaterType::river()],
        ];
    }

    public function testAnalyzeGaugingStationNameNotContainsWaterName(): void
    {
        $this->expectException(WaterNameIsEmptyException::class);

        $this->nameAnalyzer->analyze('', 'г.Гомель', self::VALID_HYDROLOGICAL_KNOWLEDGE_CODE);
    }

    public function testAnalyzeWaterNameWithNotSupportedWaterType(): void
    {
        $this->expectException(WaterTypeIsNotSupportedException::class);

        $this->nameAnalyzer->analyze('о.Телецкое', '', self::VALID_HYDROLOGICAL_KNOWLEDGE_CODE);
    }

    public function testAnalyzeWaterNameOnlyContainsWaterType(): void
    {
        $this->expectException(WaterTypeIsNotSupportedException::class);

        $this->nameAnalyzer->analyze('река', '', self::VALID_HYDROLOGICAL_KNOWLEDGE_CODE);
    }

    public function testKnownRiverWithTypoByHydrologicalCodeMustBeReplaced(): void
    {
        $expectedValidName = 'Ока Cаянская';
        $hydrologicalKnowledgeCode = '116200669';

        [, $waterName] = $this->nameAnalyzer->analyze('invalid river name', '', $hydrologicalKnowledgeCode);

        $this->assertEquals($expectedValidName, $waterName);
    }
}
