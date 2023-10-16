<?php

namespace Tests\Functional\Doctrine\Constraint\NotContainsEntities\Mock;

class ValidatorSubjectWithPrivateProperty
{
    private $privateData;

    public function __construct($privateData)
    {
        $this->privateData = $privateData;
    }
}

