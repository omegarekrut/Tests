<?php
namespace Tests\Unit\Mock;

use App\Service\ClientIp;

class ClientIpMock extends ClientIp
{
    public function __construct()
    {
    }

    public function getIp(): string
    {
        return '8.8.8.8';
    }
}
