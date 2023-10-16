<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Provider\Api\ThreadsProvider;
use App\Bridge\Xenforo\RemoteObject\ForumPost;
use App\Bridge\Xenforo\RemoteObject\ForumThread;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class ThreadsProviderTest extends TestCase
{
    use ClientApiTrait;
    use SerializerTrait;
    use CacheTrait;

    private const EXPECTED_THREAD_ID = 42;
    private const EXPECTED_POST_ID = 24;
    private const EXPECTED_RESULT_THREADS = [['thread_id' => self::EXPECTED_THREAD_ID]];
    private const EXPECTED_MOST_ACTIVE_TOPIC_IDS = [4, 8, 15, 16, 23, 42];
    private const EXPECTED_RESULT_POSTS = [['post_id' => self::EXPECTED_POST_ID]];
    private const EXPECTED_REQUEST_THREAD_IDS = [1, 2, 3];
    private const EXPECTED_REQUEST_LIMIT = 7;
    private const TEST_FORUM_USER_ID = 10;

    /**
     * @param mixed[] $threads
     *
     * @dataProvider getLatestThreads
     */
    public function testLatestThreads(array $threads): void
    {
        $this->assertCount(count(self::EXPECTED_RESULT_THREADS), $threads);
        $this->assertInstanceOf(ForumThread::class, $threads[0]);
        $this->assertEquals(self::EXPECTED_THREAD_ID, $threads[0]->getId());
    }

    public function getLatestThreads(): \Generator
    {
        $provider = $this->getProvider(
            'thread/latest-shopping-discussions/',
            null,
            [
                'data' => self::EXPECTED_RESULT_THREADS,
            ]
        );

        yield [$provider->getLatestShoppingDiscussions()];

        $provider = $this->getProvider(
            'thread/latest-for-right-sidebar/',
            null,
            [
                'data' => self::EXPECTED_RESULT_THREADS,
            ]
        );

        yield [$provider->getLatestForSidebar()];
    }

    public function testGetUserShoppingDiscussions(): void
    {
        $provider = $this->getProvider(
            'thread/user-shopping-discussions/',
            ['userId' => self::TEST_FORUM_USER_ID],
            ['data' => self::EXPECTED_RESULT_THREADS]
        );

        $topicIds = $provider->getUserShoppingDiscussions(self::TEST_FORUM_USER_ID);

        $this->assertCount(count(self::EXPECTED_RESULT_THREADS), $topicIds);
        $this->assertEquals(self::EXPECTED_THREAD_ID, $topicIds[0]->getId());
    }

    public function testGetWeeklyMostActiveTopicIds(): void
    {
        $provider = $this->getProvider(
            'thread/weekly-most-active-topic-ids/',
            null,
            [
                'data' => self::EXPECTED_MOST_ACTIVE_TOPIC_IDS,
            ]
        );

        $topicIds = $provider->getWeeklyMostActiveTopicIds();

        $this->assertCount(count(self::EXPECTED_MOST_ACTIVE_TOPIC_IDS), $topicIds);
        $this->assertEquals(self::EXPECTED_MOST_ACTIVE_TOPIC_IDS, $topicIds);
    }

    public function testGetShopInformation(): void
    {
        $provider = $this->getProvider(
            'thread/list/',
            [
                'threadIds' => self::EXPECTED_REQUEST_THREAD_IDS,
            ],
            [
                'data' => self::EXPECTED_RESULT_THREADS,
            ]
        );
        $threads = $provider->getInformation(self::EXPECTED_REQUEST_THREAD_IDS);

        $this->assertCount(count(self::EXPECTED_RESULT_THREADS), $threads);
        $this->assertInstanceOf(ForumThread::class, $threads[0]);
        $this->assertEquals(self::EXPECTED_THREAD_ID, $threads[0]->getId());
    }

    public function testGetLastPosts(): void
    {
        $provider = $this->getProvider(
            'thread/post-list/',
            [
                'threadIds' => self::EXPECTED_REQUEST_THREAD_IDS,
                'limit' => self::EXPECTED_REQUEST_LIMIT,
            ],
            [
                'data' => self::EXPECTED_RESULT_POSTS,
            ]
        );
        $posts = $provider->getLastPosts(self::EXPECTED_REQUEST_THREAD_IDS, self::EXPECTED_REQUEST_LIMIT);

        $this->assertCount(count(self::EXPECTED_RESULT_POSTS), $posts);
        $this->assertInstanceOf(ForumPost::class, $posts[0]);
        $this->assertEquals(self::EXPECTED_POST_ID, $posts[0]->postId);
    }

    private function getProvider($uri, $params, $returnArray): ThreadsProvider
    {
        return new ThreadsProvider(
            $this->createClientApi($uri, $params, $returnArray),
            $this->createSerializer(),
            $this->createCache()
        );
    }
}
