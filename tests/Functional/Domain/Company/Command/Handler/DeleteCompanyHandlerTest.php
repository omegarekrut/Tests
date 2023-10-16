<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Command\DeleteCompanyCommand;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Record\Common\Entity\Record;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Comment\LoadOneCommentByCompanyEmployeeAuthorWithCompanyAuthor;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleByCompanyEmployeeAuthorWithCompanyAuthor;
use Tests\Functional\TestCase;

/** @group company */
class DeleteCompanyHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;
    private CompanyRepository $companyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadArticleByCompanyEmployeeAuthorWithCompanyAuthor::class,
            LoadOneCommentByCompanyEmployeeAuthorWithCompanyAuthor::class,
        ])->getReferenceRepository();

        $this->companyRepository = $this->getContainer()->get(CompanyRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->companyRepository,
            $this->referenceRepository,
        );

        parent::tearDown();
    }

    public function testFieldsCompanyAuthorNameForRecordShouldBeNullAfterDeleteCompanyCommand(): void
    {
        $article = $this->referenceRepository->getReference(LoadArticleByCompanyEmployeeAuthorWithCompanyAuthor::REFERENCE_NAME);
        assert($article instanceof Record);

        $company = $article->getCompanyAuthor();

        $deleteCompanyCommand = new DeleteCompanyCommand();
        $deleteCompanyCommand->companyId = $company->getId();
        $this->getCommandBus()->handle($deleteCompanyCommand);

        $this->assertNull($this->companyRepository->findById($company->getId()));
        $this->assertNull($article->getCompanyAuthor());
        $this->assertNull($article->getCompanyAuthorName());
    }

    public function testFieldsCompanyAuthorNameForCommentShouldBeNullAfterDeleteCompanyCommand(): void
    {
        $comment = $this->referenceRepository->getReference(LoadOneCommentByCompanyEmployeeAuthorWithCompanyAuthor::REFERENCE_NAME);
        assert($comment instanceof Comment);

        $company = $comment->getCompanyAuthor();

        $deleteCompanyCommand = new DeleteCompanyCommand();
        $deleteCompanyCommand->companyId = $company->getId();
        $this->getCommandBus()->handle($deleteCompanyCommand);

        $this->assertNull($comment->getCompanyAuthor());
        $this->assertNull($comment->getCompanyAuthorName());
    }
}
