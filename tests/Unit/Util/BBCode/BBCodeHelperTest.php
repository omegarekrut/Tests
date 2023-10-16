<?php

namespace Tests\Unit\Util\BBCode;

use App\Util\BBCode\BBCodeHelper;
use JBBCode\Parser;
use Tests\Unit\TestCase;

class BBCodeHelperTest extends TestCase
{
    /**
     * @dataProvider getCasesForParse
     */
    public function testParseBBCodes(string $source, string $parsed, string $expectedHtml): void
    {
        /* @var BBCodeHelper */
        $bbCodeHelper = new BBCodeHelper($this->createParser($parsed));

        $this->assertEquals($expectedHtml, $bbCodeHelper->parseBBCodes($source));
    }

    /**
     * @dataProvider getCasesForRemovingCodes
     */
    public function testGetTextWithoutBBCodes(string $source, string $parsed, string $expectedText): void
    {
        /* @var BBCodeHelper */
        $bbCodeHelper = new BBCodeHelper($this->createParser($parsed));

        $this->assertEquals($expectedText, $bbCodeHelper->getTextWithoutBBcodes($source));
    }

    /**
     * @dataProvider getCasesForRemovingNewLines
     */
    public function testRemoveNewLines(string $source, string $expectedText): void
    {
        /* @var BBCodeHelper */
        $bbCodeHelper = new BBCodeHelper($this->createParser(''));

        $this->assertEquals($expectedText, $bbCodeHelper->removeNewLines($source));
    }

    private function createParser(string $willReturn): Parser
    {
        $stub = $this->createMock(Parser::class);

        $stub
            ->method('getAsText')
            ->willReturn($willReturn);

        $stub
            ->method('getAsHTML')
            ->willReturn($willReturn);

        $stub
            ->method('parse')
            ->willReturn($stub);

        return $stub;
    }

    public function getCasesForParse(): array
    {
        return [
            'parse bb codes' => [
                '[h3]до[/h3][h3]больших[/h3][u]липовых[/u] [i][i]островах,[/i][/i] [b]расположенных.[/b]',
                '<h3>до</h3><h3>больших</h3><u>липовых</u> <i><i>островах,</i></i> <b>расположенных.</b>',
                '<h3>до</h3><h3>больших</h3><u>липовых</u> <i><i>островах,</i></i> <b>расположенных.</b>',
            ],
            'parse with some unknown codes' => [
                '[h3]до[/h3][h3]больших[/h3][u]липовых[/u] [kek]аэродинамичных[/kek] [i][i]островах,[/i][/i] [b]расположенных.[/b]',
                '<h3>до</h3><h3>больших</h3><u>липовых</u> [kek]аэродинамичных[/kek] <i><i>островах,</i></i> <b>расположенных.</b>',
                '<h3>до</h3><h3>больших</h3><u>липовых</u> аэродинамичных <i><i>островах,</i></i> <b>расположенных.</b>',
            ],
            'parse with some denied codes' => [
                '[h3]до[/h3][h3]больших[/h3][u]липовых[/u] [font]цветных[/font] [i][i]островах,[/i][/i] [b]расположенных.[/b] [center]в центре[/center]',
                '<h3>до</h3><h3>больших</h3><u>липовых</u> [font]цветных[/font] <i><i>островах,</i></i> <b>расположенных.</b> [center]в центре[/center]',
                '<h3>до</h3><h3>больших</h3><u>липовых</u> цветных <i><i>островах,</i></i> <b>расположенных.</b> в центре',
            ],
        ];
    }

    public function getCasesForRemovingCodes(): array
    {
        return [
            'remove bb codes' => [
                '[h3]до[/h3] [h3]больших[/h3] [u]липовых[/u] [i][i]островах,[/i][/i] [b]расположенных.[/b]              [img]на этой картинке[/img]',
                'до больших липовых островах, расположенных. ',
                'до больших липовых островах, расположенных. ',
            ],
            'remove bb codes with some denied' => [
                '[h3]до[/h3] [h3]больших[/h3] [u]липовых[/u] [color]неизвестных[/color] [i][i]островах,[/i][/i] [b]расположенных.[/b]',
                'до больших липовых [color]неизвестных[/color] островах, расположенных.',
                'до больших липовых неизвестных островах, расположенных.',
            ],
        ];
    }

    public function getCasesForRemovingNewLines(): array
    {
        return [
            'remove new lines' => [
                '[h3]до[/h3] '.
                '[h3]больших[/h3] '.
                '[ul]липовых[/ul] [i][i]островах,[/i][/i] [b]расположенных.[/b] '.
                '[img]на этой картинке[/img]',
                '[h3]до[/h3] [h3]больших[/h3] [ul]липовых[/ul] [i][i]островах,[/i][/i] [b]расположенных.[/b] [img]на этой картинке[/img]',
            ],
        ];
    }
}
