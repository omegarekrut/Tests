<?php

namespace Tests\Unit\Module\WeeklyLetterForumTopicsProvider;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\ThreadsProviderInterface;
use App\Module\WeeklyLetterForumTopicsProvider\WeeklyLetterForumTopicsProvider;
use Tests\Unit\TestCase;

class WeeklyLetterForumTopicsProviderTest extends TestCase
{
    private const EXPECTED_THREADS_RESULT = [4, 8, 15, 16, 23, 42];

    public function testGetTopicIds(): void
    {
        $weeklyLetterForumTopicsProvider = new WeeklyLetterForumTopicsProvider(
            $this->createForumApiWithThreadProvider(
                'getWeeklyMostActiveTopicIds',
                self::EXPECTED_THREADS_RESULT
            )
        );

        $topicsIds = $weeklyLetterForumTopicsProvider->getTopicsIds();

        $this->assertEquals(self::EXPECTED_THREADS_RESULT, $topicsIds);
    }

    public function testGetTopicsByIds(): void
    {
        $weeklyLetterForumTopicsProvider = new WeeklyLetterForumTopicsProvider(
            $this->createForumApiWithThreadProvider(
                'getInformation',
                self::EXPECTED_THREADS_RESULT
            )
        );

        $topicsIds = $weeklyLetterForumTopicsProvider->getTopicsByIds(self::EXPECTED_THREADS_RESULT);

        $this->assertEquals(self::EXPECTED_THREADS_RESULT, $topicsIds);
    }

    /**
     * @param int[] $result
     */
    private function createForumApiWithThreadProvider(string $method, array $result): ForumApiInterface
    {
        $threadProvider = $this->createMock(ThreadsProviderInterface::class);
        $threadProvider
            ->expects($this->once())
            ->method($method)
            ->willReturn($result);

        $forumApi = $this->createMock(ForumApiInterface::class);
        $forumApi
            ->expects($this->any())
            ->method('threads')
            ->willReturn($threadProvider);

        return $forumApi;
    }
}
