<?php
declare(strict_types=1);
namespace TRAW\NotificationsFramework\Events\Configuration;

/**
 * Class AllowedTablesEvent
 */
final class AllowedTablesEvent
{
    private array $excludedTables = [
        'tx_notifications_framework_configuration',
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
        $excludedTables = $this->excludedTables;
        $filteredTables = array_filter($allowedTables, static function (mixed $table) use ($excludedTables): bool {
            return is_string($table)
                && $table !== ''
                && !in_array($table, $excludedTables, true)
                && isset($GLOBALS['TCA'][$table]);
        });

        $this->allowedTables = array_values(
            array_unique(
                array_merge($this->allowedTables, $filteredTables)
            )
        );
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
        $excludedTables = $this->excludedTables;
        $filteredTables = array_filter($allowedTablesToRemove, static function (mixed $table) use ($excludedTables): bool {
            return is_string($table)
                && $table !== ''
                && !in_array($table, $excludedTables, true)
                && isset($GLOBALS['TCA'][$table]);
        });

        $this->allowedTables = array_values(
            array_filter(
                $this->allowedTables,
                static function (string $table) use ($filteredTables): bool {
                    return !in_array($table, $filteredTables, true);
                }
            )
        );
    }
}