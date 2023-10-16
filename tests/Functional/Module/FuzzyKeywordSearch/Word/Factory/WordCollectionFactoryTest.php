<?php

namespace Tests\Functional\Module\FuzzyKeywordSearch\Word\Factory;

use App\Module\FuzzyKeywordSearch\Word\Factory\WordsParser;
use App\Module\FuzzyKeywordSearch\Word\Factory\WordCollectionFactory;
use App\Module\FuzzyKeywordSearch\Word\Factory\WordFactory;
use App\Module\FuzzyKeywordSearch\Word\Word;
use App\Module\FuzzyKeywordSearch\Word\WordCollection;
use Tests\Functional\TestCase;

class WordCollectionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var WordFactory $wordFactory */
        $wordFactory = $this->getContainer()->get(WordFactory::class);
        $wordsParser = new WordsParser();

        $wordCollectionFactory = new WordCollectionFactory($wordsParser, $wordFactory);

        $createdWordCollection = $wordCollectionFactory->createFromString('прекрасный мир иллюзий');

        $this->assertInstanceOf(WordCollection::class, $createdWordCollection);
        $this->assertCount(3, $createdWordCollection);

        /** @var Word $firstWord */
        $firstWord = $createdWordCollection->first();
        $this->assertEquals(0, $firstWord->positionInString);
        $this->assertEquals('прекрасный', $firstWord->originalForm);
        $this->assertEquals('ПРЕКРАСНЫЙ', $firstWord->baseForm);

        /** @var Word $secondWord */
        $secondWord = $createdWordCollection->next();
        $this->assertEquals(1, $secondWord->positionInString);
        $this->assertEquals('мир', $secondWord->originalForm);
        $this->assertEquals('МИР', $secondWord->baseForm);

        /** @var Word $thirdWord */
        $thirdWord = $createdWordCollection->next();
        $this->assertEquals(2, $thirdWord->positionInString);
        $this->assertEquals('иллюзий', $thirdWord->originalForm);
        $this->assertEquals('ИЛЛЮЗИЯ', $thirdWord->baseForm);
    }
}
