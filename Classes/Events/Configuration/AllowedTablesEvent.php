<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Configuration;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Notification;

/**
 * Class AllowedTablesEvent
 */
final class AllowedTablesEvent
{
    private array $excludedTables = [
        Configuration::TABLE_NAME,
        Notification::TABLE_NAME,
    ];

    /**
     * @var array
     */
    private array $allowedTables = [];

    /**
     * @param array $allowedTables
     */
    public function __construct(array $allowedTables)
    {
        $this->addAllowedTables($allowedTables);
    }

    /**
     * @return array
     */
    public function getAllowedTables(): array
    {
        return $this->allowedTables;
    }

    /**
     * Adds valid table names to the list of allowed tables.
     *
     * This method filters out:
     * - Empty strings
     * - framework tables
     * - Tables not registered in the global TCA
     *
     * Duplicates are automatically removed, and the resulting array is reindexed.
     *
     * @param string[] $allowedTables Array of table names to add.
     *
     * @return void
     */
    public function addAllowedTables(array $allowedTables): void
    {
        $excluded = array_flip($this->excludedTables);

        $filtered = array_filter(
            $allowedTables,
            static function (mixed $table) use ($excluded): bool {
                return is_string($table)
                    && $table !== ''
                    && !isset($excluded[$table])
                    && isset($GLOBALS['TCA'][$table]);
            }
        );

        $this->allowedTables = array_values(array_unique([...$this->allowedTables, ...$filtered]));
    }

    /**
     * Removes specified table names from the list of allowed tables.
     *
     * This method filters out:
     * - Empty strings
     * - framework tables
     * - Tables not registered in the global TCA
     *
     * If a table is listed for removal but not present in the allowed tables,
     * it is silently ignored. The resulting array is reindexed.
     *
     * @param string[] $allowedTablesToRemove Array of table names to remove.
     *
     * @return void
     */
    public function removeAllowedTables(array $allowedTablesToRemove): void
    {
        $excluded = array_flip($this->excludedTables);

        $toRemove = array_filter(
            $allowedTablesToRemove,
            static function (mixed $table) use ($excluded): bool {
                return is_string($table)
                    && $table !== ''
                    && !isset($excluded[$table])
                    && isset($GLOBALS['TCA'][$table]);
            }
        );

        $toRemoveFlipped = array_flip($toRemove);
        $this->allowedTables = array_values(
            array_filter(
                $this->allowedTables,
                static function (string $table) use ($toRemoveFlipped): bool {
                    return !isset($toRemoveFlipped[$table]);
                }
            )
        );
    }
}