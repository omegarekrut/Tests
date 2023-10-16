<?php

namespace Tests\Functional\Command;

use App\Command\BuildRssScheduleCommand;
use App\Domain\Rss\RssFeed\RssFeedResourceStorage;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class BuildRssScheduleCommandTest extends TestCase
{
    private const MESSAGE_BUILD_SUCCESSFULLY = 'Rss document is successfully collected'.PHP_EOL;
    private const MESSAGE_BUILD_NOT_NECESSARY = 'Not necessary to regenerate rss file'.PHP_EOL;

    /** @var CommandTester */
    private $commandTester;
    private $parametersToExecute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([LoadTackles::class]);

        $builtRssFeedFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('rss_', false).'.xml';
        $rssFeedResourceStorage = new RssFeedResourceStorage($builtRssFeedFilename);

        $consoleCommand = new BuildRssScheduleCommand($this->getCommandBus(), $rssFeedResourceStorage);

        $application = $this->getConsoleApplicationWithCommand($consoleCommand);

        $command = $application->find('app:rss:build');
        $this->commandTester = $this->getCommandTester($command);

        $this->parametersToExecute = [
            'command' => $command->getName(),
        ];
    }

    protected function tearDown(): void
    {
        unset($this->commandTester, $this->parametersToExecute);

        parent::tearDown();
    }

    public function testBuildRssFileWithDefaultFilename(): void
    {
        $this->commandTester->execute($this->parametersToExecute);

        $this->assertEquals(self::MESSAGE_BUILD_SUCCESSFULLY, $this->commandTester->getDisplay());
    }

    public function testBuildRssFileWithDefaultFilenameTwice(): void
    {
        $this->commandTester->execute($this->parametersToExecute);
        $this->commandTester->execute($this->parametersToExecute);

        $this->assertEquals(self::MESSAGE_BUILD_NOT_NECESSARY, $this->commandTester->getDisplay());
    }

    public function testBuildRssFileWithDefaultFilenameTwiceForced(): void
    {
        $this->parametersToExecute['--force'] = true;

        $this->commandTester->execute($this->parametersToExecute);
        $this->commandTester->execute($this->parametersToExecute);

        $this->assertEquals(self::MESSAGE_BUILD_SUCCESSFULLY, $this->commandTester->getDisplay());
    }
}
