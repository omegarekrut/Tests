<?php

namespace Tests\Unit\Mock;

use App\Util\Cookie\CookieCollection;
use App\Util\Cookie\Cookie;

class CookieCollectionMock extends CookieCollection
{
    public function getLast(): ?Cookie
    {
        return count($this->cookies) ? end($this->cookies) : null;
    }

    /**
     * @return Cookie[]
     */
    public function getAll(): array
    {
        return $this->cookies;
    }
}
