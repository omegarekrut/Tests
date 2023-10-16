<?php

namespace Tests\Functional;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\Finder\Finder;

class LoadFixturesTest extends TestCase
{
    /**
     * @dataProvider getFixtureClasses
     */
    public function testLoadAllFixtures($fixtureClass): void
    {
        $referenceRepository = $this->loadFixtures([$fixtureClass])->getReferenceRepository();

        $this->assertInstanceOf(ReferenceRepository::class, $referenceRepository);
    }

    public function getFixtureClasses(): array
    {
        $fixtureFileNames = $this->findAllFixtureFilesAndRequire();
        $fixtureClassNames = [];

        $declared = get_declared_classes();
        sort($declared);

        foreach ($declared as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $sourceFile = $reflectionClass->getFileName();

            if (in_array($sourceFile, $fixtureFileNames) && !$this->isTransient($className)) {
                $fixtureClassNames[$className] = [$className];
            }
        }

        return $fixtureClassNames;
    }

    private function findAllFixtureFilesAndRequire(): array
    {
        $fixtureFileNames = [];

        $finder = new Finder();
        $finder->in([
            dirname(__DIR__).'/../tests/DataFixtures/ORM',
        ])->name('/\.php$/');

        foreach ($finder as $file) {
            $file = realpath($file->getPathname());
            $fixtureFileNames[] = $file;

            require_once $file;
        }

        return $fixtureFileNames;
    }

    private function isTransient($className): bool
    {
        $rc = new \ReflectionClass($className);
        if ($rc->isAbstract()) return true;

        $interfaces = class_implements($className);

        return in_array(FixtureInterface::class, $interfaces) ? false : true;
    }
}
