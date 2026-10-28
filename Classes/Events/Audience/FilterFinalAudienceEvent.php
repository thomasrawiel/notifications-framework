<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Audience;

use TRAW\NotificationsFramework\Domain\Model\Configuration;

class FilterFinalAudienceEvent
{
    public function __construct(private Configuration $configuration, private array $users)
    {
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): void
    {
        $this->users = $users;
    }
}
