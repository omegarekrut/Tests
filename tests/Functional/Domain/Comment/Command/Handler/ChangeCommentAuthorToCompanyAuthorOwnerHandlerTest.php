<?php

namespace Tests\Functional\Domain\Comment\Command\Handler;

use App\Domain\Comment\Command\ChangeCommentAuthorToCompanyAuthorOwnerCommand;
use App\Domain\Comment\Entity\Comment;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithCompanyAuthor;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithCompanyAuthorWithoutCompanyOwner;
use Tests\Functional\TestCase;

/**
 * @group comment
 */
class ChangeCommentAuthorToCompanyAuthorOwnerHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadCommentWithCompanyAuthor::class,
            LoadCommentWithCompanyAuthorWithoutCompanyOwner::class,
        ])->getReferenceRepository();
    }

    public function testUpdateWithCompanyOwner(): void
    {
        $comment = $this->referenceRepository->getReference(LoadCommentWithCompanyAuthor::REFERENCE_NAME);
        assert($comment instanceof Comment);
        $changeCommentAuthorToCompanyAuthorOwnerCommand = new ChangeCommentAuthorToCompanyAuthorOwnerCommand($comment);

        $this->getCommandBus()->handle($changeCommentAuthorToCompanyAuthorOwnerCommand);

        $this->assertEquals($comment->getAuthor()->getId(), $comment->getCompanyAuthor()->getOwner()->getId());
        $this->assertEquals($comment->getAuthor()->getUsername(), $comment->getAuthor()->getUsername());
        $this->assertNotEquals('DELETED', $comment->getAuthor()->getUsername());
    }

    public function testUpdateWithoutCompanyOwner(): void
    {
        $comment = $this->referenceRepository->getReference(LoadCommentWithCompanyAuthorWithoutCompanyOwner::REFERENCE_NAME);
        assert($comment instanceof Comment);
        $changeCommentAuthorToCompanyAuthorOwnerCommand = new ChangeCommentAuthorToCompanyAuthorOwnerCommand($comment);

        $this->getCommandBus()->handle($changeCommentAuthorToCompanyAuthorOwnerCommand);

        $this->assertEquals($comment->getAuthor()->getId(), $comment->getCompanyAuthor()->getOwner()->getId());
        $this->assertEquals('DELETED', $comment->getAuthor()->getUsername());
    }
}
