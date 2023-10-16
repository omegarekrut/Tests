<?php

namespace Tests\Functional\Module\Rss\Twig\CharacterDataDecorator;

use App\Module\Rss\Twig\CharacterDataDecorator\CharacterDataDecoratorTokenParser;
use Tests\Functional\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @group rss
 */
class CharacterDataDecoratorTokenParserTest extends TestCase
{
    private const TEMPLATE_NAME = 'cdata-template';

    public function testCDataContentMustBeDecoratedDuringRendering(): void
    {
        $templateLoader = new ArrayLoader([
            self::TEMPLATE_NAME => '{% cdata %}test{% endcdata %}',
        ]);

        $twig = new Environment($templateLoader);
        $twig->addTokenParser(new CharacterDataDecoratorTokenParser());

        $renderedTemplate = $twig->render(self::TEMPLATE_NAME);

        $this->assertEquals('<![CDATA[test]]>', $renderedTemplate);
    }
}
