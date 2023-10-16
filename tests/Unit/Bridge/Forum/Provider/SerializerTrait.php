<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

trait SerializerTrait
{
    public function createSerializer(): SerializerInterface
    {
        return new Serializer([
            new ObjectNormalizer(null,
                new CamelCaseToSnakeCaseNameConverter())
        ], [
            new JsonEncoder()
        ]);
    }
}
