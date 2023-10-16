<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Exception\ErrorCreateForumUserException;
use App\Bridge\Xenforo\Provider\Api\UserProvider;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\City;
use App\Domain\User\Entity\ValueObject\FishingInformation;
use App\Domain\User\Entity\ValueObject\Gender;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use App\Util\Coordinates\Coordinates;
use DateTime;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class UserProviderTest extends TestCase
{
    use ClientApiTrait;

    public function testDeleteSpamUser(): void
    {
        $provider = new UserProvider($this->createClientApi('/user/delete-spam-user', [
            'userId' => 42,
        ]));
        $provider->deleteSpamUser(42);
    }

    public function testHardDeleteUser(): void
    {
        $provider = new UserProvider($this->createClientApi('/user/delete', [
            'userId' => 42,
            'mode' => 'remove',
        ]));
        $provider->deleteUser(42, true);
    }

    public function testSoftDeleteUser(): void
    {
        $provider = new UserProvider($this->createClientApi('/user/delete', [
            'userId' => 42,
            'mode' => '',
        ]));
        $provider->deleteUser(42, false);
    }

    public function testUpdatePassword(): void
    {
        $provider = new UserProvider($this->createClientApi('/password/update', [
            'userId' => 42,
            'password' => 'newpassword',
        ]));
        $provider->updatePassword(42, 'newpassword');
    }

    public function testCreate(): void
    {
        $provider = new UserProvider($this->createClientApi('/user/create', [
            'user' => [
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => '123456',
            ],
        ], ['userId' => 6]));

        $forumUserId = $provider->create('test', 'test@example.com', '123456');

        $this->assertEquals(6, $forumUserId);
    }

    public function testCreateException(): void
    {
        $this->expectException(ErrorCreateForumUserException::class);
        $this->expectExceptionMessage('User not created on forum');

        $provider = new UserProvider($this->createClientApi('/user/create', [
            'user' => [
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => '123456',
            ],
        ], ['userId' => 0]));

        $provider->create('test', 'test@example.com', 123456);
    }

    public function testUpdateDefaultUser(): void
    {
        $defaultUser = $this->createDefaultUser();
        $expectedData = [
            'userId' => 42,
            'profile' => [
                'location' => 'Nsk',
                'website' => '',
            ],
            'about_html' => 'About me',
            'user' => [
                'custom_title' => '',
                'username' => 'test',
                'email' => 'test@example.com',
            ],
            'custom_fields' => [
                'gender' => 'male',
                'name' => 'Ivan',
            ],
        ];

        $provider = new UserProvider($this->createClientApi('/user/update', $expectedData));
        $provider->update($defaultUser);
    }

    public function testUpdateUserWithBirthDay(): void
    {
        $userWithBirthday = $this->createUserWithBirthday();
        $expectedData = [
            'userId' => 42,
            'profile' => [
                'location' => 'Nsk',
                'website' => '',
            ],
            'about_html' => 'About me',
            'user' => [
                'custom_title' => '',
                'username' => 'test',
                'email' => 'test@example.com',
            ],
            'custom_fields' => [
                'gender' => 'male',
                'name' => 'Ivan',
            ],
            'dob_day' => 2,
            'dob_month' => 12,
            'dob_year' => 1995,
        ];

        $provider = new UserProvider($this->createClientApi('/user/update', $expectedData));
        $provider->update($userWithBirthday);
    }

    private function createDefaultUser(): User
    {
        $lastVisit = new LastVisit($this->getFaker()->ipv4, new DateTime());

        $defaultUser = new User(
            'test',
            'test@example.com',
            'password',
            new PasswordHashingOptions(),
            $lastVisit
        );
        $defaultUser->rewriteProfileBasicInformationFromDTO((object) [
                'login' => $defaultUser->getLogin(),
                'email' => $defaultUser->getEmailAddress(),
                'showEmail' => false,
                'gender' => Gender::MALE(),
                'birthdate' => null,
                'name' => 'Ivan',
                'city' => new City('Russia', 'Nsk', new Coordinates($this->getFaker()->latitude, $this->getFaker()->longitude)),
        ])
            ->rewriteFishingInformation(new FishingInformation(null, 'About me'))
            ->setForumUserId(42);

        return $defaultUser;
    }

    private function createUserWithBirthday(): User
    {
        $userWithBirthday = $this->createDefaultUser();

        $userWithBirthday->rewriteProfileBasicInformationFromDTO((object) [
            'login' => $userWithBirthday->getLogin(),
            'email' => $userWithBirthday->getEmailAddress(),
            'showEmail' => $userWithBirthday->getEmail()->isShowed(),
            'gender' => Gender::MALE(),
            'birthdate' => \DateTime::createFromFormat('Y-m-d', '1995-12-02'),
            'name' => $userWithBirthday->getName(),
            'city' => $userWithBirthday->getCity(),
        ])
            ->rewriteFishingInformation(new FishingInformation(null, 'About me'))
            ->setForumUserId(42);

        return $userWithBirthday;
    }
}
