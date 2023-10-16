<?php

namespace Helper;

use Codeception\Module;
use Tests\Traits\FakerFactoryTrait;
use Tests\Traits\FileSystemTrait;

class Acceptance extends Module
{
    use FakerFactoryTrait {
        getFaker as public;
    }

    use FileSystemTrait {
        loadLastEmailMessage as loadLastEmailMessageFromDirectory;
    }

    public function loadLastEmailMessage(): string
    {
        $mailDirectory = implode(DIRECTORY_SEPARATOR, [
            dirname(dirname(dirname(__DIR__))),
            'var',
            'log',
            'dev',
            'mail'
        ]);

        return $this->loadLastEmailMessageFromDirectory($mailDirectory);
    }

    public function getCurrentUrl(): ?string
    {
        if ($this->getModule('PhpBrowser')->client->getInternalResponse() === null) {
            return null;
        }

        return $this->getModule('PhpBrowser')->_getCurrentUri();
    }
}
