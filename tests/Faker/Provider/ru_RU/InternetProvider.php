<?php

namespace Tests\Faker\Provider\ru_RU;

use Faker\Provider\Internet;

class InternetProvider extends Internet
{
    protected static $userNameFormats = array(
        '{{firstName}}##',
        '?{{lastName}}',
    );

    /**
     * Login length on site from 2 to 16 characters
     */
    public function userName(): string
    {
        return mb_substr(parent::userName(), 0, 10);
    }
}
