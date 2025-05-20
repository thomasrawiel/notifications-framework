<?php

namespace TRAW\NotificationsFramework\Events\Configuration;

/**
 * Class AllowedTablesEvent
 */
final class AllowedTablesEvent
{
    /**
     * @var array
     */
    private array $allowedTables = [];

    /**
     * @param array $allowedTables
     */
    public function __construct(array $allowedTables)
    {
        $this->addAllowedTables(...$allowedTables);
    }

    /**
     * @return array
     */
    public function getAllowedTables(): array
    {
        return $this->allowedTables;
    }

    /**
     * @param string ...$allowedTables
     *
     * @return void
     */
    public function addAllowedTables(string ...$allowedTables): void
    {
        $filteredTables = array_filter($allowedTables, static function (string $table): bool {
            return $table !== ''
                && $table !== 'tx_notifications_framework_configuration'
                && isset($GLOBALS['TCA'][$table]);
        });

        $this->allowedTables = array_values(
            array_unique(
                array_merge($this->allowedTables, $filteredTables)
            )
        );
    }
}