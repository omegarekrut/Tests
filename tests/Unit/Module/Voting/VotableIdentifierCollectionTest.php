<?php

namespace Tests\Unit\Module\Voting;

use App\Module\Voting\Collection\VotableIdentifierCollection;
use App\Module\Voting\Entity\VotableIdentifier;
use InvalidArgumentException;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class VotableIdentifierCollectionTest extends TestCase
{
    public function testCollectCantBeCreatedWithNotVotableIdentifierItems(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item must be instance of');

        new VotableIdentifierCollection([$this]);
    }

    public function testCollectionHaveCount(): void
    {
        $votes = new VotableIdentifierCollection([
            new VotableIdentifier('1', 'a'),
            new VotableIdentifier('2', 'b'),
            new VotableIdentifier('4', 'c'),
        ]);

        $this->assertCount(3, $votes);
    }

    public function testCollectionCanBeFilteredByType(): void
    {
        $identifiers = new VotableIdentifierCollection([
            $firstId = new VotableIdentifier('1', 'a'),
            new VotableIdentifier('2', 'b'),
            $secondId = new VotableIdentifier('3', 'a'),
        ]);

        $filteredIdentifiers = $identifiers->filterByType('a');

        $this->assertCount(2, $filteredIdentifiers);
        $this->assertContains($firstId, $filteredIdentifiers);
        $this->assertContains($secondId, $filteredIdentifiers);
    }

    public function testMapFunction(): void
    {
        $identifiers = new VotableIdentifierCollection([
            new VotableIdentifier('5', 'a'),
            new VotableIdentifier('3', 'b'),
            new VotableIdentifier('4', 'a'),
        ]);

        $keys = $identifiers->map(function(VotableIdentifier $row) {
            return $row->getKey();
        });

        $this->assertEquals([5, 3, 4], $keys);
    }

    public function testContainsFunction(): void
    {
        $identifiers = new VotableIdentifierCollection([
            new VotableIdentifier('5', 'a'),
            new VotableIdentifier('3', 'b'),
            new VotableIdentifier('4', 'a'),
        ]);

        $this->assertTrue($identifiers->contains(new VotableIdentifier('3', 'b')));
        $this->assertFalse($identifiers->contains(new VotableIdentifier('33', 'bb')));
    }

    public function testFindKeysForType(): void
    {
        $identifiers = new VotableIdentifierCollection([
            new VotableIdentifier('5', 'a'),
            new VotableIdentifier('4', 'b'),
            new VotableIdentifier('3', 'a'),
        ]);

        $keys = $identifiers->findKeysForType('a');

        $this->assertEquals([5, 3], $keys);
    }
}
