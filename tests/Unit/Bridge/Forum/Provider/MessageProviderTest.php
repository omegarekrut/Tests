<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Provider\Api\MessageProvider;
use Tests\Unit\TestCase;

/**
 * @group forum-provider
 */
class MessageProviderTest extends TestCase
{
    use ClientApiTrait;

    public function testSendWarning()
    {
        $toUserId = 1;
        $fromUserId = 2;
        $subject = 'Message subject';
        $message = 'Message text';

        $provider = new MessageProvider($this->createClientApi('/message/warning', [
            'toUserId' => $toUserId,
            'fromUserId' => $fromUserId,
            'warning_definition_id' => 0,
            'filled_warning_definition_id' => 0,
            'custom_title' => $subject,
            'points_enable' => true,
            'points' => 1,
            'expiry_enable' => true,
            'expiry_value' => 1,
            'expiry_unit' => 'months',
            'notes' => $message,
            'start_conversation' => true,
            'conversation_title' => $subject,
            'conversation_message' => $message,
            'open_invite' => false,
            'conversation_locked' => false,
        ]));
        $provider->sendWarning($toUserId, $fromUserId, $subject, $message);
    }
}
