<?php

namespace Tests\Unit\Mock;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcherMock implements EventDispatcherInterface
{
    private $dispatchedEvents = [];

    /**
     * @param Event $event
     */
    public function dispatch($event, string $eventName = null): void
    {
        $eventName = $eventName ?? get_class($event);

        if (!isset($this->dispatchedEvents[$eventName])) {
            $this->dispatchedEvents[$eventName] = [];
        }

        $this->dispatchedEvents[$eventName][] = $event;
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }

    public function findLatestDispatchedEventByName(string $eventName): ?Event
    {
        $events = $this->dispatchedEvents[$eventName] ?? [];

        return (count($events) > 0) ? $events[count($events) - 1] : null;
    }

    public function addListener($eventName, $listener, $priority = 0): void
    {
        throwException(new \RuntimeException('Not implemented'));
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        throwException(new \RuntimeException('Not implemented'));
    }

    public function removeListener($eventName, $listener): void
    {
        throwException(new \RuntimeException('Not implemented'));
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        throwException(new \RuntimeException('Not implemented'));
    }

    public function getListeners($eventName = null): array
    {
        throwException(new \RuntimeException('Not implemented'));
    }

    public function getListenerPriority($eventName, $listener): ?int
    {
        throwException(new \RuntimeException('Not implemented'));
    }

    public function hasListeners($eventName = null): bool
    {
        throwException(new \RuntimeException('Not implemented'));
    }
}
