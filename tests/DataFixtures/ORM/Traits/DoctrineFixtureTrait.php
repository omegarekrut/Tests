<?php

namespace Tests\DataFixtures\ORM\Traits;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;

trait DoctrineFixtureTrait
{
    /**
     * Отключение auto increment перед сохранением записи,
     * что бы можно было сохранить запись с определенным id
     *
     * @param ObjectManager $manager
     * @param mixed $entity
     */
    protected function persistWithoutAutoIncrement(ObjectManager $manager, $entity): void
    {
        $metaData = $manager->getClassMetaData(get_class($entity));
        $metaData->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->persist($entity);
        $metaData->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
    }
}
