<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Utility;

final class ImageFieldPerTableEvent
{
    public function __construct(private array $tableConfiguration)
    {
    }

    public function getTableConfiguration(): array
    {
        return $this->tableConfiguration;
    }

    public function setTableConfiguration(array $tableConfiguration): void
    {
        $this->tableConfiguration = $tableConfiguration;
    }

    public function addOrReplaceTableConfiguration(string $table, array $fields): void
    {
        $this->tableConfiguration[$table] = $fields;
    }
}
