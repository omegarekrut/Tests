<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Interfaces\FindUserForForumInterface;
use App\Bridge\Xenforo\Provider\Api\StatisticsProvider;
use App\Bridge\Xenforo\RemoteObject\ForumUser;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use DateTimeInterface;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class StatisticsProviderTest extends TestCase
{
    use ClientApiTrait;
    use SerializerTrait;

    private const FORUM_USER_ID = 42;
    private const SITE_USER_ID = 6;

    private $userIdentifierResolver;
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userIdentifierResolver = $this->createUserIdentifierResolver(self::FORUM_USER_ID, self::SITE_USER_ID);
        $this->serializer = $this->createSerializer();
    }

    public function testGetCountThanks(): void
    {
        $periodStart = Carbon::now()->subDay();
        $periodEnd = Carbon::now();

        $provider = new StatisticsProvider(
            $this->createClientApi(
                '/stat/count-likes-for-user-for-period/',
                [
                    'userId' => self::FORUM_USER_ID,
                    'periodStart' => $periodStart->format(DateTimeInterface::ATOM),
                    'periodEnd' => $periodEnd->format(DateTimeInterface::ATOM),
                ],
                [
                    'data' => 3,
                ]
            ),
            $this->serializer,
            $this->userIdentifierResolver
        );

        $this->assertEquals(3, $provider->getCountThanksForPeriod(self::FORUM_USER_ID, $periodStart, $periodEnd));
    }

    public function testGetManOfTheWeek(): void
    {
        $expectedManOfWeek = [
            'user_id' => self::FORUM_USER_ID,
            'username' => 'nickname',
            'avatar' => 'http://image.com',
            'count_thanks' => 10,
        ];

        $provider = new StatisticsProvider(
            $this->createClientApi(
                '/stat/man-of-the-week/',
                null,
                [
                    'data' => $expectedManOfWeek,
                ]
            ),
            $this->serializer,
            $this->userIdentifierResolver
        );

        $user = $provider->getManOfTheWeek();

        $this->assertInstanceOf(ForumUser::class, $user);
        $this->assertEquals($expectedManOfWeek['user_id'], $user->userId);
        $this->assertEquals($expectedManOfWeek['username'], $user->username);
        $this->assertEquals($expectedManOfWeek['avatar'], $user->avatar);
        $this->assertEquals($expectedManOfWeek['count_thanks'], $user->countThanks);
        $this->assertEquals(self::SITE_USER_ID, $user->siteUserId);
    }

    private function createUserIdentifierResolver(int $resolveById, int $resultId): FindUserForForumInterface
    {
        $repository = $this->createMock(FindUserForForumInterface::class);

        $repository
            ->expects($this->any())
            ->method('findByForumUserId')
            ->with($resolveById)
            ->willReturnCallback(function () use ($resultId) {
                $user = $this->createMock(User::class);
                $user->expects($this->once())
                    ->method('getId')
                    ->willReturn($resultId);

                return $user;
            });

        return $repository;
    }
}
