<?php

namespace Tests\Functional\Domain\User\Service;

use App\Domain\User\Service\MentionService;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;
use Tests\DataFixtures\ORM\User\LoadUserWithSpaceInUsername;
use Tests\Functional\RepositoryTestCase;

/**
 * @group user
 */
class MentionServiceTest extends RepositoryTestCase
{
    /** @var MentionService */
    private $mentionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentionService = $this->getContainer()->get(MentionService::class);
    }

    protected function tearDown(): void
    {
        unset($this->mentionService);

        parent::tearDown();
    }

    public function testGetMentionsOfThreeUsersAndOneNotExisting(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithDotInUsername::class,
            LoadTestUser::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $userWithDotInLogin = $referenceRepository->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);

        $text = sprintf(
            'Comment @notExistingUsername mail@mail.com containing @%s mentions @%s, @%s',
            $userWithDotInLogin->getLogin(),
            $user->getLogin(),
            $admin->getLogin()
        );

        $mentionedUsers = $this->mentionService->getMentionedUsersFromText($text);

        $this->assertCount(3, $mentionedUsers);
    }

    public function testGetUserMentionFromComment(): void
    {
        $referenceRepository = $this->loadFixtures([LoadUserWithSpaceInUsername::class])->getReferenceRepository();

        $userWithSpaceInLogin = $referenceRepository->getReference(LoadUserWithSpaceInUsername::REFERENCE_NAME);

        $text = sprintf('it @%s comment containing mention ', $userWithSpaceInLogin->getLogin());

        $mentionedUsers = $this->mentionService->getMentionedUsersFromText($text);

        $this->assertCount(1, $mentionedUsers);
    }

    public function testGetUserIfShortLoginThereIsInLongLogin(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadModeratorAdvancedUser::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        $moderatorAdvancedUser = $referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME);
        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);

        $this->assertStringContainsString($moderator->getLogin(), $moderatorAdvancedUser->getLogin());

        $text = sprintf('@%s comment @%s mentions', $moderatorAdvancedUser->getLogin(), $moderator->getLogin());

        $mentionedUsers = $this->mentionService->getMentionedUsersFromText($text);

        $this->assertCount(2, $mentionedUsers);
    }

    public function testMentionedUserWithThisUsernameDoesNotExist(): void
    {
        $mentionedUsersFromText = $this->mentionService->getMentionedUsersFromText('@notExistUser привет');

        $this->assertCount(0, $mentionedUsersFromText);
    }
}
