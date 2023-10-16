<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Provider\Api\ProfileProvider;
use App\Domain\User\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class ProfileProviderTest extends TestCase
{
    use ClientApiTrait;
    use SerializerTrait;

    private const EXPECTED_PROFILE_DATA = [
        'smallAvatar' => 'http://image/small',
        'mediumAvatar' => 'http://image/medium',
        'largeAvatar' => 'http://image/large',
        'unreadMessagesCount' => 1,
        'notificationsCount' => 2,
        'messagesCount' => 3,
    ];

    /** @var SerializerInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createSerializer();
    }

    public function testGetProfile(): void
    {
        $provider = new ProfileProvider($this->createClientApi(
            'profile/1/',
            null,
            self::EXPECTED_PROFILE_DATA
        ), $this->serializer);

        $profile = $provider->getProfile(1);

        $this->assertEquals(self::EXPECTED_PROFILE_DATA['smallAvatar'], $profile->smallAvatar);
        $this->assertEquals(self::EXPECTED_PROFILE_DATA['mediumAvatar'], $profile->mediumAvatar);
        $this->assertEquals(self::EXPECTED_PROFILE_DATA['largeAvatar'], $profile->largeAvatar);
        $this->assertEquals(self::EXPECTED_PROFILE_DATA['unreadMessagesCount'], $profile->unreadMessagesCount);
        $this->assertEquals(self::EXPECTED_PROFILE_DATA['notificationsCount'], $profile->notificationsCount);
        $this->assertEquals(self::EXPECTED_PROFILE_DATA['messagesCount'], $profile->messagesCount);
    }

    public function testDeleteAvatar(): void
    {
        $provider = new ProfileProvider($this->createClientApi(
            'profile/delete-avatar',
            [
                'userId' => 1,
            ]
        ), $this->serializer);
        $provider->deleteAvatar($this->createUser());
    }

    public function testUploadAvatar(): void
    {
        $file = $this->getDataFixturesFolder().'pixel.png';
        $provider = new ProfileProvider($this->createClientApi(
            'profile/upload-avatar',
            [
                [
                    'name' => 'userId',
                    'contents' => 1,
                ],
                'avatar' => [
                    'name' => 'avatar',
                    'filename' => 'pixel.png',
                    'contents' => file_get_contents($file),
                ],
            ]
        ), $this->serializer);
        $provider->uploadAvatar($this->createUser(), $file);
    }

    private function createUser(): User
    {
        $user = $this->createMock(User::class);

        $user->expects($this->once())
            ->method('getForumUserId')
            ->willReturn(1);

        return $user;
    }
}
