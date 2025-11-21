<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Audience;

use TRAW\NotificationsFramework\Domain\Model\Configuration;

final class GetAdditionalUsersEvent
{
    private array $additionalUsers = [];

    public function __construct(private readonly Configuration $configuration)
    {
    }

    public function getAdditionalUsers(): array
    {
        return $this->additionalUsers;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setAdditionalUsers(array $additionalUsers): void
    {
        $this->additionalUsers = $additionalUsers;
    }
}
