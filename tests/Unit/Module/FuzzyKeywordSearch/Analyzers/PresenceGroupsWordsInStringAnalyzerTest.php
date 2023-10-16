<?php

namespace Tests\Unit\Module\FuzzyKeywordSearch\Analyzers;

use App\Module\FuzzyKeywordSearch\Analyzer\PresenceGroupsWordsInStringAnalyzer;
use App\Module\FuzzyKeywordSearch\Word\GroupedWordsCollection;
use App\Module\FuzzyKeywordSearch\Word\WordCollection;
use App\Module\FuzzyKeywordSearch\Word\Word;
use Tests\Unit\TestCase;

class PresenceGroupsWordsInStringAnalyzerTest extends TestCase
{
    private $presenceGroupsWordsInStringAnalyzer;
    private $originalString;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenceGroupsWordsInStringAnalyzer = new PresenceGroupsWordsInStringAnalyzer();

        $this->originalString = '
            Lorem Ipsum является стандартной статьёй рыбалки на тему "рыбы" для текстов на латинице с начала XVI века.
            Существует компания рога и копыта, производящая прикормку для <b>карасей</b>.
            Рог изобилия является предсказатель "клева" и он описан в статье на тему рыбалки...
        ';
    }

    protected function tearDown(): void
    {
        unset($this->presenceGroupsWordsInStringAnalyzer);

        parent::tearDown();
    }

    public function testAnalyzeWithGroupWords(): void
    {
        $words = [
            [
                2 => 'статьёй',
                3 => 'рыбалки',
                4 => 'на',
                5 => 'тему',
            ],
        ];

        $groupsWordsCollection = $this->createGroupsWordsCollection($words);

        $foundGroupWords = $this->presenceGroupsWordsInStringAnalyzer->analyze($groupsWordsCollection, $this->originalString);

        $this->assertEquals(
            'статьёй рыбалки на тему',
            $foundGroupWords
        );
    }

    public function testFindTwoGroupsWithNearGroupsWords(): void
    {
        $words = [
            [
                2 => 'статьёй',
                3 => 'рыбалки',
                4 => 'на',
                5 => 'тему',
            ],
            [
                9 => 'текстов',
                10 => 'на',
                11 => 'латинице',
            ],
            [
                18 => 'изобилия',
                19 => 'предсказатель',
                20 => '"клева"',
            ],
        ];

        $groupsWordsCollection = $this->createGroupsWordsCollection($words);

        $foundGroupWords = $this->presenceGroupsWordsInStringAnalyzer->analyze($groupsWordsCollection, $this->originalString);

        $this->assertEquals(
            'статьёй рыбалки на тему',
            $foundGroupWords
        );
    }

    public function testFoundEmptyGroups(): void
    {
        $words = [
            [
                13 => 'планета',
                14 => 'санктум',
            ],
        ];

        $groupsWordsCollection = $this->createGroupsWordsCollection($words);

        $foundGroupWords = $this->presenceGroupsWordsInStringAnalyzer->analyze($groupsWordsCollection, $this->originalString);

        $this->assertEmpty($foundGroupWords);
    }

    private function createGroupsWordsCollection(array $groupsWords): GroupedWordsCollection
    {
        $groupsWordsCollection = new GroupedWordsCollection();

        foreach ($groupsWords as $groupWords) {
            $wordCollection = $this->createWordCollection($groupWords);

            $groupsWordsCollection->add($wordCollection);
        }

        return $groupsWordsCollection;
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
