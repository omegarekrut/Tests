<?php

namespace Tests\Unit\Mock;

use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ViolationBuilderMock implements ConstraintViolationBuilderInterface
{
    public function atPath($path)
    {
        return $this;
    }

    public function setParameter($key, $value)
    {
        return $this;
    }

    public function setParameters(array $parameters)
    {
        return $this;
    }

    public function setTranslationDomain($translationDomain)
    {
        return $this;
    }

    public function setInvalidValue($invalidValue)
    {
        return $this;
    }

    public function setPlural($number)
    {
        return $this;
    }

    public function setCode($code)
    {
        return $this;
    }

    public function setCause($cause)
    {
        return $this;
    }

    public function addViolation()
    {
        // TODO: Implement addViolation() method.
    }
}
