<?php

namespace Tests\Unit\Mock;

use ZenRss\Builder;

class ZenRssBuilderMock extends Builder
{
    private $path;
    private $saved = false;

    public function __construct()
    {
    }

    public function save(string $path): bool
    {
        $this->path = $path;
        $this->saved = true;

        return true;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function isSaved(): bool
    {
        return $this->saved;
    }
}
