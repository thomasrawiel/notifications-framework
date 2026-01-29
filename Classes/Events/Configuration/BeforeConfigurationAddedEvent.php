<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

use TRAW\NotificationsFramework\Events\AbstractEvent;

/**
 * Class BeforeConfigurationAddedEvent
 */
final class BeforeConfigurationAddedEvent
{
    private bool $addConfiguration = true;

    /**
     * @param array              $data
     * @param AbstractEvent|null $event
     */
    public function __construct(private int|string $newId, private array $data = [], private ?AbstractEvent $event = null)
    {
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
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

    public function isConfigurationIsAllowed(): bool
    {
        return $this->configurationIsAallowed;
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
