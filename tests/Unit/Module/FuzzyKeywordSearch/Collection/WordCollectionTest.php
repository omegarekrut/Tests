<?php

namespace Tests\Unit\Module\FuzzyKeywordSearch\Collection;

use App\Module\FuzzyKeywordSearch\Word\WordCollection;
use App\Module\FuzzyKeywordSearch\Word\Word;
use Tests\Unit\TestCase;

class WordCollectionTest extends TestCase
{
    public function testGetString(): void
    {
        $words = ['прекрасный', 'мир', 'иллюзий'];

        $wordCollection = $this->createWordCollection($words);

        $this->assertEquals('прекрасный мир иллюзий', $wordCollection->toString());
    }

    public function testIsContainsWord(): void
    {
        $words = ['прекрасный', 'мир', 'иллюзий'];

        $wordCollection = $this->createWordCollection($words);

        $word = new Word(1, 'прекрасный', 'ПРЕКРАСНЫЙ');

        $this->assertTrue($wordCollection->containsWord($word));
    }

    public function testContainsWordCollection(): void
    {
        $words = ['прекрасный', 'мир', 'иллюзий'];

        $firstWordCollection = $this->createWordCollection($words);

        $identicalWordCollection = $this->createWordCollection($words);

        $isContainsWordCollection = $firstWordCollection->containsWords($identicalWordCollection);

        $this->assertTrue($isContainsWordCollection);
    }

    public function testNotContainsWordCollection(): void
    {
        $words = ['прекрасный', 'мир', 'иллюзий'];

        $firstWordCollection = $this->createWordCollection($words);

        $words = ['призрачный', 'мир', 'иллюзий'];

        $otherWordCollection = $this->createWordCollection($words);

        $isContainsWordCollection = $firstWordCollection->containsWords($otherWordCollection);

        $this->assertFalse($isContainsWordCollection);
    }

    public function testIntersect(): void
    {
        $words = ['прекрасный', 'мир', 'иллюзий'];

        $firstWordCollection = $this->createWordCollection($words);

        $words = ['призрачный', 'мир', 'иллюзий'];

        $secondWordCollection = $this->createWordCollection($words);

        $sameWordCollection = $firstWordCollection->intersect($secondWordCollection);

        $this->assertNotEmpty($sameWordCollection);

        $this->assertTrue($sameWordCollection->containsWord($firstWordCollection->get(1)));
        $this->assertTrue($sameWordCollection->containsWord($firstWordCollection->get(2)));
    }

    public function testGroupByAdjacentWords(): void
    {
        $words = [
            1 => 'автомобиль',
            3 => 'прекрасный',
            4 => 'мир',
            5 => 'иллюзий',
            13 => 'планета',
            14 => 'санктум',
            22 => 'диалог',
            33 => 'фит',
        ];

        $wordCollection = $this->createWordCollection($words);

        $foundGroups = $wordCollection->groupByAdjacentWords(2);

        $this->assertNotEmpty($foundGroups);

        $exceptedFoundWordsInGroups = [
            [1, 2],
            [2, 3],
            [4, 5],
        ];

        /** @var WordCollection $foundGroup */
        foreach ($foundGroups as $key => $foundGroup) {
            foreach ($exceptedFoundWordsInGroups[$key] as $indexOfWordInGroup) {
                $this->assertTrue($foundGroup->contains($wordCollection->get($indexOfWordInGroup)));
            }
        }
    }

    private function createWordCollection(array $words): WordCollection
    {
        $wordCollection = new WordCollection();

        foreach ($words as $positionInString => $word) {
            $wordCollection->add(new Word($positionInString, $word, strtoupper($word)));
        }

        return $wordCollection;
    }
}
