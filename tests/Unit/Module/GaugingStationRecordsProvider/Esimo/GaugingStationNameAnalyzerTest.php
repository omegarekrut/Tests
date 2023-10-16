<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\Esimo;

use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationNameAnalyzer;
use Tests\Unit\TestCase;

class GaugingStationNameAnalyzerTest extends TestCase
{
    /**
     * @dataProvider getNames
     */
    public function testAnalyze($name, $expectedAnalyzedName): void
    {
        $nameAnalyzer = new GaugingStationNameAnalyzer();

        $analyzedName = $nameAnalyzer->analyze($name);

        $this->assertEquals($expectedAnalyzedName, $analyzedName);
    }

    public function getNames(): array
    {
        return [
            ['г.Гомель,река Сож', 'Гомель'],
            ['Гомель,река Сож', 'Гомель'],
            ['ГОМЕЛЬ', 'Гомель'],
        ];
    }
}
