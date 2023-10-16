<?php

namespace Tests\DataFixtures\ORM\Log;

use App\Domain\Log\Entity\ActionLog;
use App\Domain\Log\Entity\ValueObject\LoggingAction;
use App\Domain\Log\Entity\ValueObject\LoggingRequestContext;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class LoadActionLog extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'action-log';
    private const COUNT = 30;

    private \Faker\Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $log = new ActionLog(
                $this->getReference(LoadAdminUser::REFERENCE_NAME),
                new LoggingAction('Some\\HideCommand', ['some test parameter' => 'value']),
                new LoggingRequestContext('App\Controller\Admin\Test\Logs::test', $this->generator->url()),
                $this->generator->ipv4()
            );

            $manager->persist($log);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $log);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdminUser::class,
        ];
    }
}
