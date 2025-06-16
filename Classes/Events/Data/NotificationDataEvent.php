<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

class NotificationDataEvent
{
    public function __construct(private array $data, private array $configuration)
    {
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}