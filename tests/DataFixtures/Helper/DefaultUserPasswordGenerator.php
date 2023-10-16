<?php

namespace Tests\DataFixtures\Helper;

class DefaultUserPasswordGenerator
{
    private const DEFAULT_USER_PASSWORD = '123456';

    public function generate(): string
    {
        return self::DEFAULT_USER_PASSWORD;
    }
}
