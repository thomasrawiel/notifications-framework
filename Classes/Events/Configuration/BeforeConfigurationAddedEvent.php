<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

use TRAW\NotificationsFramework\Events\AbstractEvent;

/**
 * Class BeforeConfigurationAddedEvent
 */
final class BeforeConfigurationAddedEvent
{
    /**
     * @param array              $data
     * @param AbstractEvent|null $event
     */
    public function __construct(private array $data = [], private ?AbstractEvent $event = null)
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

    /**
     * @return AbstractEvent|null
     */
    public function getEvent(): ?AbstractEvent
    {
        return $this->event;
    }
}