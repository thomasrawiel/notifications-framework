<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Audience;

class FilterUsersFromConfigurationEvent
{
    public function __construct(private array $users)
    {
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
