<?php

namespace Tests\Unit\Twig\Url;

use App\Twig\Url\IdnToUtf8Translator;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class IdnToUtf8TranslatorTest extends TestCase
{
    /**
     * @dataProvider dataIdnToUtf8Translate
     */
    public function testIdnToUtf8Translate(string $asciiDomainName, string $expectedDomainName): void
    {
        $idnToUtf8Translator = new IdnToUtf8Translator();

        $this->assertEquals($expectedDomainName, $idnToUtf8Translator($asciiDomainName));
    }

    /**
     * @return mixed[]
     */
    public function dataIdnToUtf8Translate(): array
    {
        return [
            'cyrillicTranslate' => [
                'http://www.xn--90aadvbahgm3abrdge8n.xn--p1ai',
                'http://www.сибирскийрыболов.рф',
            ],
            'asciiMustBeUnchangedWithPathOnCyrillic' => [
                'http://www.foo.bar/помощь',
                'http://www.foo.bar/помощь',
            ],
            'cyrillicTranslateWithPathOnCyrillic' => [
                'http://www.xn--90aadvbahgm3abrdge8n.xn--p1ai/помощь',
                'http://www.сибирскийрыболов.рф/помощь',
            ],
            'cyrillicTranslateWithQueryOnCyrillic' => [
                'http://www.xn--90aadvbahgm3abrdge8n.xn--p1ai/?q=помощь',
                'http://www.сибирскийрыболов.рф/?q=помощь',
            ],
            'asciiMustBeUnchanged' => [
                'http://www.foo.bar',
                'http://www.foo.bar',
            ],
            'asciiMustBeUnchangedWithPathOnAscii' => [
                'http://www.foo.bar/help',
                'http://www.foo.bar/help',
            ],
            'asciiTranslateWithQueryOnCyrillic' => [
                'http://www.foo.bar/?q=помощь',
                'http://www.foo.bar/?q=помощь',
            ],
        ];
    }
}
