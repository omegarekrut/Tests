<?php

namespace Tests\Unit\Doctrine\Constraint\ThroughTransferObject;

use App\Doctrine\Constraint\ThroughTransferObject\DuplicateFinder;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Unit\TestCase;

/**
 * @group constraint
 */
class DuplicateFinderTest extends TestCase
{
    public function testNotConfigured()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate finder must be configured. Use "createWithConfiguration" method');

        $duplicateFinder = new DuplicateFinder($this->createEntityManager());
        $duplicateFinder->findDuplicateByCriteria([]);
    }

    public function testDuplicateSearch()
    {
        $expectedEntityClass = get_class($this);
        $expectedCriteria = [
            'foo' => 'bar',
        ];
        $expectedDuplicate = new \stdClass();

        $entityManager = $this->createEntityManager($expectedEntityClass, $expectedCriteria, [
            $this,
            $expectedDuplicate,
        ]);

        $duplicateFinder = new DuplicateFinder($entityManager);
        $duplicateFinder = $duplicateFinder->createWithConfiguration(
            $expectedEntityClass,
            'findBy',
            $this
        );

        $duplicates = $duplicateFinder->findDuplicateByCriteria($expectedCriteria);

        $this->assertContains($expectedDuplicate, $duplicates);
        $this->assertNotContains($this, $duplicates);
    }

    private function createEntityManager(?string $entityClass = null, ?array $expectedCriteria = null, ?array $searchResult = null): EntityManagerInterface
    {
        $repository = $expectedCriteria && $searchResult ? $this->createRepository($searchResult, $expectedCriteria) : null;
        $stub = $this->createMock(EntityManagerInterface::class);

        if ($entityClass) {
            $stub
                ->expects($this->once())
                ->method('getRepository')
                ->with($entityClass)
                ->willReturn($repository);
        } else {
            $stub
                ->expects($this->never())
                ->method('getRepository');
        }

        return $stub;
    }

    private function createRepository($findResult, array $expectedCriteria)
    {
        return new class($this, $findResult, $expectedCriteria) {
            private $tester;
            private $findResult;
            private $expectedCriteria;

            public function __construct(TestCase $tester, $findResult, $expectedCriteria)
            {
                $this->tester = $tester;
                $this->findResult = $findResult;
                $this->expectedCriteria = $expectedCriteria;
            }

            public function findBy($criteria)
            {
                $this->tester->assertEquals($this->expectedCriteria, $criteria);
                return $this->findResult;
            }
        };
    }
}
