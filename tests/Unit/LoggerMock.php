<?php

namespace Tests\Unit;

use Psr\Log\LoggerInterface;

/**
 * Тестовый logger
 */
class LoggerMock implements LoggerInterface
{
    private $messages = [];

    public function emergency($message, array $context = array())
    {
        $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log('critical', $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log('notice', $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        $this->messages[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getMessages(bool $clear = true): array
    {
        $message = $this->messages;

        if ($clear === true) {
            $this->messages = [];
        }

        return $message;
    }
}

