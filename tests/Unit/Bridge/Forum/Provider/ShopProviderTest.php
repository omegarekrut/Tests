<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Provider\Api\ShopProvider;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class ShopProviderTest extends TestCase
{
    use ClientApiTrait;

    public function testCreate()
    {
        $provider = $this->getProvider(
            'thread/create/',
            [
                'nodeId' => 8,
                'threadId' => 0,
                'title' => 'Title',
                'message_html' => 'Message',
                'discussion_open' => 1,
                'poll' => [
                    'question' => 'Оцените этот магазин',
                    'max_votes_type' => 'number',
                    'max_votes_value' => 1,
                    'view_results_unvoted' => 1,
                    'new_responses' => [
                        '1 (очень плохо)',
                        '2 (плохо)',
                        '3 (удовлетворительно)',
                        '4 (хорошо)',
                        '5 (отлично)',
                    ],
                ],
            ],
            [
                'threadId' => 42,
            ]);
        $this->assertEquals(42, $provider->create('Title', 'Message'));
    }

    public function testUpdate()
    {
        $provider = $this->getProvider(
            'thread/update/',
            [
                'nodeId' => 8,
                'threadId' => 42,
                'title' => 'Title',
                'message_html' => 'Message',
                'discussion_open' => 1,
            ],
            [
                'threadId' => 42,
            ]);
        $this->assertEquals(42, $provider->update(42, 'Title', 'Message'));
    }

    private function getProvider($uri, $params, $returnArray): ShopProvider
    {
        return new ShopProvider(
            $this->createClientApi($uri, $params, $returnArray)
        );
    }
}
