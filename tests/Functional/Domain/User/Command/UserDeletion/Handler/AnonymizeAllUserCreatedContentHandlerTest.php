<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\User\Command\Deleting\AnonymizeAllUserCreatedContentCommand;
use App\Domain\User\Entity\User;
use App\Module\Voting\Entity\AnonymousVoter;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\User\LoadUserWhoVotedForRecord;
use Tests\Functional\TestCase;

/**
 * @group user
 */
class AnonymizeAllUserCreatedContentHandlerTest extends TestCase
{
    private VoteStorage $voteStorage;
    private RecordRepository $recordRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
        $this->recordRepository = $this->getEntityManager()->getRepository(Record::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->voteStorage,
            $this->recordRepository
        );

        parent::tearDown();
    }

    public function testAfterHandlingUserVotesMustBeAnonymized(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWhoVotedForRecord::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadUserWhoVotedForRecord::REFERENCE_NAME);
        assert($user instanceof User);

        $userVotes = $this->voteStorage->getVoterVotes($user);

        $command = new AnonymizeAllUserCreatedContentCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->voteStorage->getVoterVotes($user));

        foreach ($userVotes as $vote) {
            $this->assertInstanceOf(AnonymousVoter::class, $vote->getVoter());
            $this->assertEquals($user->getUsername(), $vote->getVoter()->getUsername());
        }
    }
}
