<?php

namespace TRAW\NotificationsFramework\Events\Data;

use TYPO3\CMS\Extbase\Persistence\Generic\Query;

class FilteredNotificationsFetchedEvent
{
    public function __construct(private Query $query)
    {
    }

    public function setQuery(Query $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }
}