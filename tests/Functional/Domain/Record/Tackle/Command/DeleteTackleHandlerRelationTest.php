<?php

namespace Tests\Functional\Domain\Record\Tackle\Command;

use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleReview;
use Doctrine\ORM\EntityRepository;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Tests\Functional\RepositoryTestCase;

class DeleteTackleHandlerRelationTest extends RepositoryTestCase
{
    private $referenceRepository;
    private $repository;
    private $tackleReviewRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTackles::class,
            LoadTackleReviews::class,
        ])->getReferenceRepository();

        $this->repository = $this->getRepository(Tackle::class);
        $this->tackleReviewRepository = $this->getRepository(TackleReview::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->repository,
            $this->tackleReviewRepository
        );

        parent::tearDown();
    }

    public function testDeleteRelation(): void
    {
        $tackle = $this->referenceRepository->getReference(LoadTackles::getRandReferenceName());
        assert($tackle instanceof Tackle);

        $expectedReviewCount = $tackle->getReviews()->count();

        $this->repository->delete($tackle);

        $this->assertEmpty($this->tackleReviewRepository->findOneByTackle($tackle->getId()));

        $this->getEntityManager()->getFilters()->disable('soft_deleteable');

        $this->assertEquals($expectedReviewCount, $this->getCountInDatabase($this->tackleReviewRepository, 'tackle', $tackle->getId()));
    }

    private function getCountInDatabase(EntityRepository $repository, string $foreignKey, int $id): int
    {
        return $repository
            ->createQueryBuilder('t')
            ->select('count(t.id)')
            ->where(sprintf('t.%s = :id', $foreignKey))
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
