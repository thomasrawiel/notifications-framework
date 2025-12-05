<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Configuration;

use TYPO3\CMS\Core\DataHandling\DataHandler;

final class RecordAllowedEvent
{
    private bool $recordIsAllowed = true;

    public function __construct(private string $table, private int|string $recordId, private array $recordFieldArray, private DataHandler $dataHandler)
    {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRecordId(): int|string
    {
        return $this->recordId;
    }

    public function getRecordFieldArray(): array
    {
        return $this->recordFieldArray;
    }

    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }

    public function isRecordAllowed(): bool
    {
        return $this->recordIsAllowed;
    }

    public function setRecordIsAllowed(bool $recordIsAllowed): void
    {
        $this->recordIsAllowed = $recordIsAllowed;
    }
}
