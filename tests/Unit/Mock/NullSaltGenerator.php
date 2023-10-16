<?php
namespace Tests\Unit\Mock;

use App\Domain\User\Generator\SaltGeneratorInterface;

class NullSaltGenerator implements SaltGeneratorInterface
{
    public function generate(): ?string
    {
        return null;
    }
}
