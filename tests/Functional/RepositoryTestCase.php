<?php

namespace Tests\Functional;

use Doctrine\ORM\EntityRepository;

abstract class RepositoryTestCase extends TestCase
{
    protected function getRepository(string $entityClass): EntityRepository
    {
        return $this->getEntityManager()->getRepository($entityClass);
    }
}
