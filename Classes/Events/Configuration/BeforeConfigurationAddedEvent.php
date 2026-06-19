<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

use TRAW\NotificationsFramework\Events\AbstractEvent;

/**
 * Class BeforeConfigurationAddedEvent
 */
final class BeforeConfigurationAddedEvent
{
    private bool $addConfiguration = true;

    public function __construct(private int|string $newId, private array $data = [], private ?AbstractEvent $event = null)
    {
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getNewId(): int|string
    {
        return $this->newId;
    }

    public function getEvent(): ?AbstractEvent
    {
        return $this->event;
    }

    public function isAddConfiguration(): bool
    {
        return $this->addConfiguration;
    }

    public function setAddConfiguration(bool $addConfiguration): void
    {
        $this->addConfiguration = $addConfiguration;
    }
}
