<?php

namespace Tests\Unit\Bridge\Forum\Service;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\ThreadsProviderInterface;
use App\Service\SidebarForumTopics;
use Symfony\Component\Cache\Simple\ArrayCache;
use Tests\Unit\TestCase;

class SidebarForumTopicsTest extends TestCase
{
    private const EXPECTED_THREADS_RESULT = [['thread_id' => 1]];
    private const TEST_FORUM_USER_ID = 10;

    /**
     * @dataProvider types
     */
    public function testGetTopicsByTypeMethod(string $type, string $methodName): void
    {
        $threadProvider = $this->createThreadProviderForLatestTopics($methodName, self::EXPECTED_THREADS_RESULT);
        $forumApi = $this->createForumApi($threadProvider);

        $sidebarForumTopics = new SidebarForumTopics($forumApi, new ArrayCache());

        $result = $sidebarForumTopics->getTopicsByType($type);

        $this->assertEquals(self::EXPECTED_THREADS_RESULT, $result);
    }

    /**
     * @return mixed[]
     */
    public function types(): array
    {
        return [
            'getLatestShoppingDiscussions' => ['shopping', 'getLatestShoppingDiscussions'],
            'getLatestForSidebar' => ['right_sidebar', 'getLatestForSidebar'],
        ];
    }

    /**
     * @param mixed[] $resultThreads
     */
    private function createThreadProviderForLatestTopics(string $threadProviderMethodName, array $resultThreads): ThreadsProviderInterface
    {
        $threadProvider = $this->createMock(ThreadsProviderInterface::class);
        $threadProvider
            ->expects($this->once())
            ->method($threadProviderMethodName)
            ->willReturn($resultThreads);

        return $threadProvider;
    }

    private function createForumApi(ThreadsProviderInterface $threadProvider): ForumApiInterface
    {
        $forumApi = $this->createMock(ForumApiInterface::class);
        $forumApi
            ->expects($this->any())
            ->method('threads')
            ->willReturn($threadProvider);

        return $forumApi;
    }

    public function testGetUserShoppingTopicsMethod(): void
    {
        $threadProvider = $this->createThreadProviderForUserTopics(
            self::TEST_FORUM_USER_ID,
            self::EXPECTED_THREADS_RESULT
        );
        $forumApi = $this->createForumApi($threadProvider);

        $sidebarForumTopics = new SidebarForumTopics($forumApi, new ArrayCache());

        $result = $sidebarForumTopics->getUserShoppingTopics('user_ads', self::TEST_FORUM_USER_ID);

        $this->assertEquals(self::EXPECTED_THREADS_RESULT, $result);
    }

    /**
     * @param mixed[] $resultThreads
     */
    private function createThreadProviderForUserTopics(int $forumUserId, array $resultThreads): ThreadsProviderInterface
    {
        $threadProvider = $this->createMock(ThreadsProviderInterface::class);
        $threadProvider
            ->expects($this->once())
            ->method('getUserShoppingDiscussions')
            ->with($forumUserId)
            ->willReturn($resultThreads);

        return $threadProvider;
    }
}
