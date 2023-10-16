<?php

namespace Tests\Functional\Module\FuzzyKeywordSearch;

use App\Module\FuzzyKeywordSearch\Analyzer\PresenceGroupsWordsInStringAnalyzer;
use App\Module\FuzzyKeywordSearch\Word\Factory\WordCollectionFactory;
use App\Module\FuzzyKeywordSearch\FuzzyKeywordSearcher;
use Tests\Functional\TestCase;

class FuzzyKeywordSearchServiceTest extends TestCase
{
    /** @var WordCollectionFactory */
    private $wordCollectionFactory;
    private $fuzzyKeywordSearchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wordCollectionFactory = $this->getContainer()->get(WordCollectionFactory::class);

        $presenceGroupsWordsInStringAnalyzer = new PresenceGroupsWordsInStringAnalyzer();

        $this->fuzzyKeywordSearchService = new FuzzyKeywordSearcher($presenceGroupsWordsInStringAnalyzer);
    }

    protected function tearDown(): void
    {
        unset($this->wordCollectionFactory, $this->fuzzyKeywordSearchService);

        parent::tearDown();
    }

    public function testExistSearchKeywordInSourceString(): void
    {
        $sourceString = '
            Lorem Ipsum является стандартной статьёй рыбалки на тему "рыбы" для текстов на латинице с начала XVI века.
            Существует компания рога и копыта, производящая прикормку для <b>карасей</b>.
            Рог изобилия является предсказатель "клева" и он описан в статье на тему рыбалки...
        ';

        $sourceWordCollection = $this->wordCollectionFactory->createFromString($sourceString);
        $searchWordCollection = $this->wordCollectionFactory->createFromString('предсказатель клева');

        $result = $this->fuzzyKeywordSearchService->searchKeywordInSourceString($sourceString, $sourceWordCollection, $searchWordCollection);

        $this->assertNotEmpty($result);
        $this->assertEquals('предсказатель "клева"', $result);
    }

    public function testEmptyFoundSearchKeywordInSourceString(): void
    {
        $sourceString = '
            Lorem Ipsum является стандартной статьёй рыбалки на тему "рыбы" для текстов на латинице с начала XVI века.
            Существует компания рога и копыта, производящая прикормку для <b>карасей</b>.
            Рог изобилия является предсказатель "клева" и он описан в статье на тему рыбалки...
        ';

        $sourceWordCollection = $this->wordCollectionFactory->createFromString($sourceString);
        $searchWordCollection = $this->wordCollectionFactory->createFromString('предсказатель лавин');

        $result = $this->fuzzyKeywordSearchService->searchKeywordInSourceString($sourceString, $sourceWordCollection, $searchWordCollection);

        $this->assertEmpty($result);
    }
}
