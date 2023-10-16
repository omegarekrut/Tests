<?php

namespace Tests\Unit\Util\Security\UrlMatcher;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class UrlMatcherMock implements UrlMatcherInterface
{
    private $context;
    private $matchResult;
    private $pathInfoMatchParam;

    public function match($pathInfo)
    {
        $this->pathInfoMatchParam = $pathInfo;

        if ($this->matchResult === null) {
            throw new ResourceNotFoundException('Ресурс не найден');
        }

        $callback = $this->matchResult;

        return $callback($this, $pathInfo);
    }

    /**
     * @param null|callable $matchResult
     *
     * @return UrlMatcherMock
     */
    public function setMatchResult(callable $matchResult = null): self
    {
        $this->matchResult = $matchResult;

        return $this;
    }

    public function getPathInfoMatchParam()
    {
        return $this->pathInfoMatchParam;
    }

    public function setContext(RequestContext $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): ?RequestContext
    {
        return $this->context;
    }
}
