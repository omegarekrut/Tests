<?php

namespace Tests\Functional\Domain\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\LinkedAccount;
use App\Domain\User\Repository\UserRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUsersWithLinkedAccount;
use Tests\DataFixtures\ORM\User\LoadUserWithResetToken;
use Tests\Functional\RepositoryTestCase;

class UserRepositoryTest extends RepositoryTestCase
{
    private User $user;
    private UserRepository $userRepository;
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadNumberedUsers::class,
            LoadTestUser::class,
            LoadMostActiveUser::class,
            LoadModeratorAdvancedUser::class,
            LoadModeratorUser::class,
            LoadUsersWithLinkedAccount::class,
            LoadUserWithResetToken::class,
        ])->getReferenceRepository();

        $this->user = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->userRepository = $this->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->user,
            $this->userRepository
        );

        parent::tearDown();
    }

    public function testFindOneByLoginOrEmail(): void
    {
        foreach ($this->getLoginsOrEmails() as $comment => $loginOrEmail) {
            $foundUser = $this->userRepository->findOneByLoginOrEmail($loginOrEmail);

            $this->assertEquals($this->user, $foundUser, $comment);
        }
    }

    public function testFindOneByLoginOrEmailFailed(): void
    {
        $notExistingUserLogin = sprintf('%s-not-existing', $this->user->getLogin());

        $foundUser = $this->userRepository->findOneByLoginOrEmail($notExistingUserLogin);

        $this->assertNull($foundUser);
    }

    public function testFindTopGlobalRatingUsers(): void
    {
        $users = $this->userRepository->findTopGlobalRatingUsers(2);

        $this->assertGreaterThanOrEqual($users[1]->getGlobalRating()->getValue(), $users[0]->getGlobalRating()->getValue());
    }

    public function testFindTopActivityRatingUsers(): void
    {
        $users = $this->userRepository->findTopActivityRatingUsers(2);

        $this->assertGreaterThanOrEqual($users[1]->getActivityRating()->getValue(), $users[0]->getActivityRating()->getValue());
    }

    public function testFindTopGlobalRatingUsersLimit(): void
    {
        $users = $this->userRepository->findTopGlobalRatingUsers(1);

         $this->assertCount(1, $users);
    }

    public function testFindTopActivityRatingUsersLimit(): void
    {
        $users = $this->userRepository->findTopActivityRatingUsers(1);

         $this->assertCount(1, $users);
    }

    /**
     * @return string[]
     */
    private function getLoginsOrEmails(): array
    {
        return [
            'by login' => $this->user->getLogin(),
            'by email' => $this->user->getEmailAddress(),
        ];
    }

    public function testFindForAutocomplete(): void
    {
        $this->assertCount(1, $this->userRepository->findForAutocomplete('most-active-user'));
        $this->assertCount(2, $this->userRepository->findForAutocomplete('moderator'));
    }

    public function testFindByLinkedAccount(): void
    {
        /** @var User $user */
        $expectedUser = $this->referenceRepository->getReference(LoadUsersWithLinkedAccount::getRandReferenceName());
        /** @var LinkedAccount $expectedLinkedAccount */
        $expectedLinkedAccount = $expectedUser->getLinkedAccounts()->current();
        $actualUser = $this->userRepository->findOneByProvider($expectedLinkedAccount->getProviderName(), $expectedLinkedAccount->getUuid());

        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testFindOneByResetPasswordToken(): void
    {
        $expectedUser = $this->referenceRepository->getReference(LoadUserWithResetToken::REFERENCE_NAME);
        $actualUser = $this->userRepository->findOneByResetPasswordToken($expectedUser->getResetPasswordToken()->getToken());

        $this->assertEquals($expectedUser, $actualUser);
    }
}
