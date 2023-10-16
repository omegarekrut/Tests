<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\SuggestedNews\Repository\SuggestedNewsRepository;
use App\Domain\User\Command\Deleting\DeleteAllUserCreatedContentCommand;
use App\Domain\User\Entity\User;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\SuggestedNews\LoadSuggestedNewsByUserFixture;
use Tests\DataFixtures\ORM\User\LoadUserWhoVotedForRecord;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\Functional\TestCase;

/**
 * @group user
 */
class DeleteAllUserCreatedContentHandlerTest extends TestCase
{
    private VoteStorage $voteStorage;
    private RecordRepository $recordRepository;
    private CompanyRepository $companyRepository;
    private SuggestedNewsRepository $suggestedNewsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
        $this->recordRepository = $this->getContainer()->get(RecordRepository::class);
        $this->companyRepository = $this->getContainer()->get(CompanyRepository::class);
        $this->suggestedNewsRepository = $this->getContainer()->get(SuggestedNewsRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->voteStorage,
            $this->recordRepository,
            $this->companyRepository,
            $this->suggestedNewsRepository,
        );

        parent::tearDown();
    }

    public function testAfterHandlingUserCommentsMustBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadUserWithComments::REFERENCE_NAME);
        assert($user instanceof User);

        $command = new DeleteAllUserCreatedContentCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->recordRepository->findAllCommentedByUser($user));
    }

    public function testAfterHandlingUserVotesMustBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWhoVotedForRecord::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadUserWhoVotedForRecord::REFERENCE_NAME);
        assert($user instanceof User);

        $command = new DeleteAllUserCreatedContentCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->voteStorage->getVoterVotes($user));
    }

    public function testAfterHandlingUserRecordsMustBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        $record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        assert($record instanceof Record);

        $user = $record->getAuthor();

        $command = new DeleteAllUserCreatedContentCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->recordRepository->findAllOwnedByUser($user));
    }

    public function testAfterHandlingUserCompaniesMustBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $user = $company->getOwner();

        $command = new DeleteAllUserCreatedContentCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertEmpty($this->companyRepository->getAllOwnedByAuthor($user));
    }

    public function testAfterHandlingUserSuggestedNewsMustBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSuggestedNewsByUserFixture::class,
        ])->getReferenceRepository();

        $suggestedNews = $referenceRepository->getReference(LoadSuggestedNewsByUserFixture::REFERENCE_NAME);
        assert($suggestedNews instanceof SuggestedNews);

        $user = $suggestedNews->getAuthor();

        $command = new DeleteAllUserCreatedContentCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertEmpty($this->suggestedNewsRepository->getAllByAuthor($user));
    }
}
