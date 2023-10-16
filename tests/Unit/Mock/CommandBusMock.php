<?php

namespace Tests\Unit\Mock;

use App\Module\TacticianQueueInterop\QueueCommand;
use League\Tactician\CommandBus;

final class CommandBusMock extends CommandBus
{
    /**
     * @var object[]
     */
    private array $handledCommands = [];

    public function __construct()
    {
        parent::__construct([]);
    }

    public function handle($command): void
    {
        if ($command instanceof QueueCommand) {
            $command = $command->getOriginalCommand();
        }

        $this->handledCommands[] = $command;
    }

    public function getLastHandledCommand(): ?object
    {
        return count($this->handledCommands) ? $this->handledCommands[count($this->handledCommands) - 1] : null;
    }

    /**
     * @return object[]
     */
    public function getAllHandledCommands(): array
    {
        return $this->handledCommands;
    }

    /**
     * @return object[]
     */
    public function getAllHandledCommandsOfClass(string $className): array
    {
        $handledCommands = array_filter($this->handledCommands, static fn ($command): bool => $command instanceof $className);

        return array_values($handledCommands);
    }

    public function isHandled(string $commandClass): bool
    {
        foreach ($this->handledCommands as $command) {
            if ($command instanceof $commandClass) {
                return true;
            }
        }

        return false;
    }
}
