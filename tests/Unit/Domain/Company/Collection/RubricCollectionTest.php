<?php

namespace Tests\Unit\Domain\Company\Collection;

use App\Domain\Company\Collection\RubricCollection;
use App\Domain\Company\Entity\Rubric;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

/**
 * @group company
 */
class RubricCollectionTest extends TestCase
{
    public function testExclude(): void
    {
        $expectedRubric = $this->createRubric('rubric one');
        $excludedRubric = $this->createRubric('rubric two');
        $rubrics = new RubricCollection([
            $expectedRubric,
            $excludedRubric,
        ]);

        $rubricsWithoutExcluded = $rubrics->exclude($excludedRubric);

        $this->assertCount(1, $rubricsWithoutExcluded);
        $this->assertTrue($expectedRubric === $rubricsWithoutExcluded->first());
    }

    private function createRubric(string $name): Rubric
    {
        return new Rubric(Uuid::uuid4(), '', $name);
    }
}
