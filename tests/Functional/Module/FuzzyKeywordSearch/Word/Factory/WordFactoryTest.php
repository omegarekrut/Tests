<?php

namespace Tests\Functional\Module\FuzzyKeywordSearch\Word\Factory;

use App\Module\FuzzyKeywordSearch\Word\Factory\WordFactory;
use App\Module\FuzzyKeywordSearch\Word\Word;
use Tests\Functional\TestCase;

class WordFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $phpMorphy = $this->getContainer()->get(\phpMorphy::class);

        $wordFactory = new WordFactory($phpMorphy);

        $createdWord = $wordFactory->create(10, '"рыбалки"');

        $this->assertInstanceOf(Word::class, $createdWord);
        $this->assertEquals(10, $createdWord->positionInString);
        $this->assertEquals('"рыбалки"', $createdWord->originalForm);
        $this->assertEquals('РЫБАЛКА', $createdWord->baseForm);
    }
}
