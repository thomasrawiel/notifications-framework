<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Audience;

class FilterGroupsFromConfigurationEvent
{
    public function __construct(private array $groups)
    {
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }
}
