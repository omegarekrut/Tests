<?php

namespace Tests\Support\Repository\Exception;

class UserNotFoundException extends \Exception
{
    protected $message = 'User not found';
}
