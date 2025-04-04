<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

use TRAW\NotificationsFramework\Events\AbstractEvent;

final class BeforeConfigurationAddedEvent
{
    public function __construct(private array $data = [], private ?AbstractEvent $event = null)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getEvent(): ?AbstractEvent
    {
        return $this->event;
    }
}