<?php

namespace TRAW\NotificationsFramework\Events\Data;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class FilteredNotificationsFetchedEvent
{
    public function __construct(private QueryInterface $query)
    {
    }

    public function setQuery(QueryInterface $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }
}