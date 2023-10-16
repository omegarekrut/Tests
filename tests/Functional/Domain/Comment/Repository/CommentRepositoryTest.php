<?php

namespace Tests\Functional\Domain\Comment\Repository;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithoutUrls;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithUrl;
use Tests\Functional\TestCase;

/**
 * @group comment
 */
class CommentRepositoryTest extends TestCase
{
    /** @var CommentRepository */
    private $commentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = $this->getEntityManager()->getRepository(Comment::class);
    }

    protected function tearDown(): void
    {
        unset($this->commentRepository);

        parent::tearDown();
    }

    public function testCommentsContainingUrlsCanBeFoundInRepository(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCommentWithUrl::class,
            LoadCommentWithoutUrls::class
        ])->getReferenceRepository();

        /** @var Comment $expectedComment */
        $expectedCommentWithUrl = $referenceRepository->getReference(LoadCommentWithUrl::REFERENCE_NAME);
        $unexpectedCommentWithoutUrl = $referenceRepository->getReference(LoadCommentWithoutUrls::REFERENCE_NAME);

        $comments = $this->commentRepository->createQueryBuilderForFindAllContainingUrls()->getQuery()->getResult();

        $this->assertContains($expectedCommentWithUrl, $comments);
        $this->assertNotContains($unexpectedCommentWithoutUrl, $comments);
    }
}
