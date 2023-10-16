<?php

namespace Tests\Unit\Mock\BulkMailSender\Mailgun;

use Http\Adapter\Guzzle7\Client;
use Mailgun\Api\Message;
use Mailgun\Hydrator\ArrayHydrator;
use Mailgun\HttpClient\RequestBuilder;

class MessagesApiMock extends Message
{
    /**
     * @var mixed[]
     */
    private $sentMessages = [];

    public function __construct()
    {
        parent::__construct(new Client(), new RequestBuilder(), new ArrayHydrator());
    }

    /**
     * @inheritDoc
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function send(string $domain, array $params)
    {
        $this->sentMessages[] = $params;
    }

    /**
     * @return mixed[]
     */
    public function getSentMessages(): array
    {
        return $this->sentMessages;
    }
}
