<?php

namespace Tests\Functional\Domain\Hashtag\Repository;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Repository\HashtagRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Landing\LoadImportantLandings;
use Tests\Functional\RepositoryTestCase;

/**
 * @group hashtags
 */
class HashtagRepositoryTest extends RepositoryTestCase
{
    /** @var HashtagRepository */
    private $repository;

    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadImportantLandings::class,
        ])->getReferenceRepository();

        $this->repository = $this->getRepository(Hashtag::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->repository
        );

        parent::tearDown();
    }

    public function testFindByName(): void
    {
        /** @var Hashtag $expectedHashtag */
        $expectedHashtag = $this->referenceRepository->getReference(
            LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING)
        );
        $actualHashtags = $this->repository->findByName($expectedHashtag->getName());

        $this->assertCount(1, $actualHashtags);
        $this->assertEquals($expectedHashtag, $actualHashtags[0]);
    }
}
