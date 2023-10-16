<?php

namespace Tests\DataFixtures\ORM\Notification;

use App\Domain\Notification\Entity\CustomNotification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadCustomNotification extends Fixture
{
    public const REFERENCE_NAME = 'custom-notification';

    public function load(ObjectManager $manager): void
    {
        $customNotification = new CustomNotification(
            Uuid::uuid4(),
            'Это тестовое сообщение с поддрежкой html <a href="https://ya.ru">посетите не пропустите</a>',
            'Тестовое сообщение'
        );

        $this->addReference(self::REFERENCE_NAME, $customNotification);
        $manager->persist($customNotification);

        $manager->flush();
    }
}
